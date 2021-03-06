<?php

/**
The Original-Code is from:
https://github.com/googleads/googleads-php-lib/blob/master/examples/Dfp/v201805/LineItemService/GetAllLineItems.php

I've altered a few things to just get Line-Items that ended yesterday, my changes are marked by beginning with "ME:"

*/
/**
 * Copyright 2016 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Google\AdsApi\Examples\Dfp\v201805\LineItemService;
require __DIR__ . '/vendor/autoload.php';
use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\Dfp\DfpServices;
use Google\AdsApi\Dfp\DfpSession;
use Google\AdsApi\Dfp\DfpSessionBuilder;
use Google\AdsApi\Dfp\Util\v201805\StatementBuilder;
use Google\AdsApi\Dfp\v201805\LineItemService;
use DateTime;
use DateTimeZone;
use Google\AdsApi\Dfp\Util\v201805\DfpDateTimes;

/*ME: delete old data*/
$myfile = './ids.txt';
$current = "";
file_put_contents($myfile, $current);

/**
 * This example gets all line items.
 *
 * <p>It is meant to be run from a command line (not as a webpage) and requires
 * that you've setup an `adsapi_php.ini` file in your home directory with your
 * API credentials and settings. See README.md for more info.
 */
class GetAllLineItems
{
    public static function runExample(
        DfpServices $dfpServices,
        DfpSession $session
    ) {
        $lineItemService = $dfpServices->get($session, LineItemService::class);
        // Create a statement to select line items.
        $pageSize = StatementBuilder::SUGGESTED_PAGE_LIMIT;
        $statementBuilder = (new StatementBuilder())->orderBy('id ASC')
            ->limit($pageSize);
        // Retrieve a small amount of line items at a time, paging
        // through until all line items have been retrieved.
        $totalResultSetSize = 0;
        do {
            $page = $lineItemService->getLineItemsByStatement(
                $statementBuilder->toStatement()
            );
            // Print out some information for each line item.
            if ($page->getResults() !== null) {
                $totalResultSetSize = $page->getTotalResultSetSize();
                $i = $page->getStartIndex();
                foreach ($page->getResults() as $lineItem) {

				//ME:  Check if LineItem is set to unlimited EndDate, if not, get EndDate
					if (!$lineItem->getunlimitedEndDateTime()){
						$liyear = $lineItem->getendDateTime()->getDate()->getYear();
						$limonth = $lineItem->getendDateTime()->getDate()->getMonth();
						$liday = $lineItem->getendDateTime()->getDate()->getDay();
						$lidate = $liday.'-'.$limonth.'-'.$liyear;
					}
					else{
						$liyear = false;
						$limonth = false;
						$liday = false;
						$lidate = false;
					}
					
				//ME:  Get current Date
					$year = date("Y");
					$month = date("m");
					$day = date("d");

					//ME:  If End-Date of Line-Item is Yesterday
						if ($liyear == $year && $limonth == $month && $liday == ($day-1)){
							printf(
								"Line item with ID ".$lineItem->getId()." and day ".$lidate." was found. Currentyear is ".$month."\n",
								$i++
							);
							//ME: Add IDs
							$file = './ids.txt';
							//ME:  Open the file to get existing content
							$current = file_get_contents($file);
							//ME:  Append a new person to the file
							$current .= $lineItem->getId()."\n";
							//ME: Write the contents back to the file
							file_put_contents($file, $current);
							//ME:  END
						}
                }
            }
            $statementBuilder->increaseOffsetBy($pageSize);
        } while ($statementBuilder->getOffset() < $totalResultSetSize);
        printf("Number of results found: %d\n", $totalResultSetSize);
    }
    public static function main()
    {
        // Generate a refreshable OAuth2 credential for authentication.
        $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile()
            ->build();
        // Construct an API session configured from a properties file and the
        // OAuth2 credentials above.
        $session = (new DfpSessionBuilder())->fromFile()
            ->withOAuth2Credential($oAuth2Credential)
            ->build();
        self::runExample(new DfpServices(), $session);
    }
}
GetAllLineItems::main();


