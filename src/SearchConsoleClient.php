<?php

namespace SchulzeFelix\SearchConsole;

use Google_Client;
use Google_Service_Webmasters;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SearchConsoleClient
{
    const CHUNK_SIZE = 25000;

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
     * @param int $rows
     * @param \Google_Service_Webmasters_SearchAnalyticsQueryRequest $request
     * @return Collection
     * @throws \Exception
     */
    public function performQuery($siteUrl, $rows, $request): Collection
    {
        $searchanalyticsResource = $this->getWebmastersService()->searchanalytics;

        $maxQueries = 2000;
        $currentRequest = 1;
        $dataRows = new Collection();

        while ($currentRequest < $maxQueries) {
            $startRow = ($currentRequest - 1) * self::CHUNK_SIZE;

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

                if (is_array($row->getKeys()) && count($row->getKeys())) {
                    $item = array_combine($request->getDimensions(), $row->getKeys());
                    $uniqueHash = $this->getUniqueItemHash($row, $request);
                } else {
                    $uniqueHash = md5(Str::random(20));
                    $item = [];
                }

                $item['clicks'] = $row->getClicks();
                $item['impressions'] = $row->getImpressions();
                $item['ctr'] = $row->getCtr();
                $item['position'] = $row->getPosition();
                $item['searchType'] = $request->getSearchType();
                $item['dataState'] = $request->getDataState();
                $item['aggregationType'] = $request->getAggregationType();

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
     */
    public function setQuotaUser(string $quotaUser)
    {
        $quotaUser = md5($quotaUser);

        $this->queryOptParams['quotaUser'] = $quotaUser;

        $guzzleConfig = $this->googleClient->getHttpClient()->getConfig();

        Arr::set($guzzleConfig, 'base_uri', Google_Client::API_BASE_PATH.'?quotaUser='.$quotaUser);

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

    /**
     * @return Google_Client
     */
    public function getGoogleClient(): Google_Client
    {
        return $this->googleClient;
    }

    /**
     * @return Google_Service_Webmasters
     */
    public function getWebmastersService(): Google_Service_Webmasters
    {
        return new Google_Service_Webmasters($this->googleClient);
    }

    /**
     * @param $row
     * @param $request
     * @return string
     */
    private function getUniqueItemHash($row, $request)
    {
        $keys = implode('', $row->getKeys());

        $filters = [];
        foreach ($request->getDimensionFilterGroups() as $dimensionFilterGroup) {
            foreach ($dimensionFilterGroup->filters as $filter) {
                $filters[] = $filter->dimension.$filter->expression.$filter->operator;
            }
        }
        $filters = implode('', $filters);

        return md5($keys.$filters.$request->getSearchType().$request->endDate.$request->startDate);
    }
}
