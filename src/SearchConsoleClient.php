<?php

namespace SchulzeFelix\SearchConsole;

use Google_Client;
use Google_Service_Webmasters;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class SearchConsoleClient
{
    const CHUNK_SIZE = 5000;

    /**
     * @var Google_Client
     */
    private $googleClient;

    private $queryOptParams = [];

    /**
     * SearchConsoleClient constructor.
     * @param Google_Client $googleClient
     * @internal param Google_Service_Webmasters $service
     */
    public function __construct(Google_Client $googleClient)
    {
        $this->googleClient = $googleClient;
    }

    /**
     * @param string $siteUrl
     * @param integer $rows
     * @param \Google_Service_Webmasters_SearchAnalyticsQueryRequest $request
     * @return Collection
     */
    public function performQuery($siteUrl, $rows, $request): Collection
    {
        $searchanalyticsResource = $this->getWebmastersService()->searchanalytics;

        $maxQueries = 2000;
        $currentRequest = 1;
        $dataRows = new Collection();

        while ($currentRequest < $maxQueries) {
            $startRow = ($currentRequest-1) * self::CHUNK_SIZE;

            $request->setRowLimit(self::CHUNK_SIZE);
            $request->setStartRow($startRow);

            $backoff = new ExponentialBackoff(10);
            $response = $backoff->execute(function () use ($searchanalyticsResource, $siteUrl, $request) {
                return $searchanalyticsResource->query($siteUrl, $request, $this->queryOptParams);
            });

            // Stop if no more rows returned
            if (count($response->getRows()) == 0) {
                break;
            }

            foreach ($response->getRows() as $row) {
                /*
                 * Use a unique hash as key to prevent duplicates caused by the query dimension problem with the google api
                 * Google give less than 5000 rows back when two or more dimension with the query dimension are choosen, repeated calls give back more rows
                 * https://productforums.google.com/forum/?hl=en#!topic/webmasters/wF_Rm9CGr4U
                 */

                $uniqueHash = md5(str_random());
                $item = [];

                if (count($row->getKeys())) {
                    $item = array_combine($request->getDimensions(), $row->getKeys());
                    $uniqueHash = md5(implode('', $row->getKeys()) . $request->getSearchType());
                }

                $item['clicks'] = $row->getClicks();
                $item['impressions'] = $row->getImpressions();
                $item['ctr'] = $row->getCtr();
                $item['position'] = $row->getPosition();
                $item['searchType'] = $request->getSearchType();

                $dataRows->put($uniqueHash, $item);
            }

            //Stop if the requested row count are reached
            if ($dataRows->count() >= $rows) {
                break;
            }

            $currentRequest++;
        }

        return $dataRows->take($rows);
    }

    /**
     * @param string $quotaUser
     *
     */
    public function setQuotaUser(string $quotaUser)
    {
        $this->queryOptParams['quotaUser'] = $quotaUser;

        $guzzleConfig = $this->googleClient->getHttpClient()->getConfig();

        array_set($guzzleConfig, 'base_uri', Google_Client::API_BASE_PATH . '?quotaUser=' . $quotaUser);

        $guzzleClient = new Client($guzzleConfig);

        $this->googleClient->setHttpClient($guzzleClient);
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken(string $accessToken)
    {
        $this->googleClient->setAccessToken($accessToken);
    }


    public function getWebmastersService(): Google_Service_Webmasters
    {
        return new Google_Service_Webmasters($this->googleClient);
    }
}
