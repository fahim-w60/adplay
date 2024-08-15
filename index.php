<?php
// Helper method to check if a campaign matches the bid request
function campaignMatchesBidRequest($campaign, $bid_request)
{
    // Check device compatibility
    if(strpos(strtolower($campaign['hs_os']), strtolower($bid_request['device']['os'])) == '')
    {
        return [];
    }
    
    // Check geolocation
    if(strtolower($campaign['country']) != strtolower($bid_request['device']['geo']['country']))
    {
        return [];
    }
    
    // Select bid by bid floor (price) and highest bidder wins.
    $selected_bids= [];
    foreach($bid_request['imp'] as $bid)
    {
        if($campaign['price'] >= $bid['bidfloor'])
        {
            array_push($selected_bids, $bid);
        }
    }


    // Check ad format and again filter selected banners
    $final_selected_bid=[];
    $dimensions= explode('x', $campaign['dimension']);
    foreach($selected_bids as $bid)
    {
        foreach($bid['banner']['format'] as $format)
        {
            if($format['w']==$dimensions[0] && $format['h']==$dimensions[1])
            {
                $final_selected_bid=$bid;
                break;
            }
        }
    }

    if(empty($final_selected_bid))
    {
        return [];
    }
    else
    {
        $campaign['bid_request']=$final_selected_bid;
    }
    
    
    return $campaign;
}

// method to select the best campaign based on the bid request
function selectBestCampaign($bid_request, $campaigns)
{
    $eligible_campaigns=[];

    foreach($bid_request as $br)
    {
        foreach($campaigns as $cmp)
        {
            array_push($eligible_campaigns, campaignMatchesBidRequest($cmp, $br));
        }
    }

    if (empty($eligible_campaigns)) {
        return null;
    }

    // If multiple campaigns are eligible, choose the one with the highest bid price
    usort($eligible_campaigns, function ($a, $b) {
        return $a['price'] <=> $b['price'];
    });

    return $eligible_campaigns[count($eligible_campaigns) - 1];
}

// method to generate the JSON response
function generateResponse($selected_campaign)
{
    $bid_request= $selected_campaign['bid_request'];
    $response = [
        'id' => $bid_request['id'],
        'imp' => [
            [
                'banner' => [
                    'w' => $bid_request['banner']['w'],
                    'h' => $bid_request['banner']['h'],
                    'pos' => $bid_request['banner']['pos'],
                    'api' => $bid_request['banner']['api'],
                    'format' => $bid_request['banner']['format'], 
                ],
                'bidfloor' => $bid_request['bidfloor'],
                'bidfloorcur' => $bid_request['bidfloorcur'],
                'secure' => $bid_request['secure'],
            ],
        ],
        'ad' => [
            [
                'campaignname' => isset($selected_campaign['campaignname'])?$selected_campaign['campaignname']:'',
                'advertiser' => isset($selected_campaign['advertiser']) ? $selected_campaign['advertiser'] : '',
                'code' => isset($selected_campaign['code']) ? $selected_campaign['code'] : '',
                'appid' => isset($selected_campaign['appid']) ? $selected_campaign['appid'] : '',
                'tld' => isset($selected_campaign['tld']) ? $selected_campaign['tld'] : '',
                'portalname' => isset($selected_campaign['portalname']) ? $selected_campaign['portalname'] : '',
                'creative_type' => isset($selected_campaign['creative_type']) ? $selected_campaign['creative_type'] : '',
                'creative_id' => isset($selected_campaign['creative_id']) ? $selected_campaign['creative_id'] : '',
                'day_capping' => isset($selected_campaign['day_capping']) ? $selected_campaign['day_capping'] : '',
                'dimension' => isset($selected_campaign['dimension']) ? $selected_campaign['dimension'] : '',
                'attribute' => isset($selected_campaign['attribute']) ? $selected_campaign['attribute'] : '',
                'url' => isset($selected_campaign['url']) ? $selected_campaign['url'] : '',
                'billing_id' => isset($selected_campaign['billing_id']) ? $selected_campaign['billing_id'] : '',
                'price' => isset($selected_campaign['price']) ? $selected_campaign['price'] : '',
                'bidtype' => isset($selected_campaign['bidtype']) ? $selected_campaign['bidtype'] : '',
                'creative_height' => isset($selected_campaign['creative_height']) ? $selected_campaign['creative_height'] : '',
                'creative_width' => isset($selected_campaign['creative_width']) ? $selected_campaign['creative_width'] : '',
                'html' => isset($selected_campaign['html']) ? $selected_campaign['html'] : '',
                'displayname' => isset($selected_campaign['displayname']) ? $selected_campaign['displayname'] : '',
                'domain' => isset($selected_campaign['domain']) ? $selected_campaign['domain'] : '',
                'tag' => isset($selected_campaign['tag']) ? $selected_campaign['tag'] : '',
                'frequency' => isset($selected_campaign['frequency']) ? $selected_campaign['frequency'] : '',
                'click_url' => isset($selected_campaign['click_url']) ? $selected_campaign['click_url'] : '',
                'statistics' => isset($selected_campaign['statistics']) ? $selected_campaign['statistics'] : '',
                'revenue' => isset($selected_campaign['revenue']) ? $selected_campaign['revenue'] : '',
            ],
        ],
        'seatbid' => [
            [
                'seat' => 'TAHIR',
                'bid' => [
                    [
                        'id' => uniqid('TAHIR'),
                        'impid' => isset($bid_request['imp'][0]['id']) ? $bid_request['imp'][0]['id'] : '',
                        'price' => isset($selected_campaign['price']) ? $selected_campaign['price'] : '',
                        'nurl' => isset($selected_campaign['url']) ? $selected_campaign['url'] : '',
                        'adm' => isset($selected_campaign['html']) ? $selected_campaign['html'] : '',
                        'iurl' => isset($selected_campaign['creative_url']) ? $selected_campaign['creative_url'] : '',
                        'cid' => isset($selected_campaign['creative_id']) ? $selected_campaign['creative_id'] : '',
                        'crid' => isset($selected_campaign['creative_id']) ? $selected_campaign['creative_id'] : '',
                        'attr' => isset($selected_campaign['attribute']) ? $selected_campaign['attribute'] : '',                        
                    ],
                ],
            ],
        ],
    ];

    return json_encode($response);
}

$bid_request = json_decode(file_get_contents('bid_request.json'), true);
$campaigns = json_decode(file_get_contents('campaigns.json'), true);
$selected_campaign = selectBestCampaign($bid_request, $campaigns);
$response = generateResponse($selected_campaign);

?>

<h3><strong>Task Info:</strong></h3>
<div>
    <table border="1" cellpadding="5px" style="border-collapse:collapse">
        <tr>
            <th>Task name</th>
            <td>Real Time Bidding scenario Implementation</td>
        </tr>
        <tr>
            <th>Task Submitted By</th>
            <td>Abdullah Muhammad Tahir <br>Software Engineer</td>
        </tr>
        <tr>
            <th>Email & Phone</th>
            <td>
                <a href="mailto:tahir.ewu@gmail.com">tahir.ewu@gmail.com</a><br>
                <a href="tel:01977755085">01977755085</a>
            </td>
        </tr>
    </table>
</div>


<div style="width: 500px;">
    <h3>Final Response:</h3><hr>
    <?= $response ?>
</div>