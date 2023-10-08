<?php
namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mail
{
    private $apiKey = '0001ea847e641931dc5359dae58e8ccc';
    private $apiSecretKey = '1f70b514699f2cdcbf3b01fa8ee47d68';
    

    public function send($toEmail, $toName, $subject, $content )
    {
        
        $mj = new Client($this->apiKey, $this->apiSecretKey, true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "fxd.work@gmail.com",
                        'Name' => "FXD"
                    ],
                    'To' => [
                        [
                            'Email' => $toEmail,
                            'Name' => $toName,
                        ]
                    ],
                    'TemplateID' => 5163556,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                        ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        
        $response->success() && ($response->getData());

    }

}
