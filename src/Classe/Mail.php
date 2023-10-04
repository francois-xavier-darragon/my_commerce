<?php
namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mail
{
    private $apiKey = '51898722ea82e707982059b38ecef349';
    private $apiSecretKey = '484c387621e949b28a32a0907165b09b';

    public function send($toEmail, $toName, $subject, $content )
    {
        
        $mj = new Client($this->apiKey, $this->apiSecretKey, true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "fxd15130@gmail",
                        'Name' => "FXD"
                    ],
                    'To' => [
                        [
                            'Email' => $toEmail,
                            'Name' => $toName,
                        ]
                    ],
                    'TemplateID' => 5153013,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                        ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
        // $response->success() && dd($response->getData());

    }
}
