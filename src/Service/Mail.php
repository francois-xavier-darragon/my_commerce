<?php
namespace App\Service;

use Mailjet\Client;
use Mailjet\Resources;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class Mail
{
    // private $apiKey = '0001ea847e641931dc5359dae58e8ccc';
    // private $apiSecretKey = '1f70b514699f2cdcbf3b01fa8ee47d68';
    
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }
    
    public function send($toEmail, $toName, $subject, $content )
    {
        
        $mj = new Client($this->params->get('api_key'), $this->params->get('api_secret_key'), true, ['version' => 'v3.1']);
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
