<?php

namespace Launchpad\Postmark;

use Postmark\PostmarkClient;
use Postmark\Models\PostmarkException;
use Postmark\Models\PostmarkAttachment;

class Postmark
{
    protected object $client;
    protected array $config;

    public function __construct()
    {
        $this->client = new PostmarkClient( $_ENV['postmark_token'] );
        $this->config = [
            'sender'        => $_ENV['postmark_sender'],
            'replyTo'       => $_ENV['postmark_reply'],
            'recipients'    => [
                'to'            => [],
                'cc'            => [],
                'bcc'           => []
            ],
            'templateData'  => [],
            'attachments'   => [],
            'inlineCss'     => true,
            'trackOpens'    => false,
        ];
    }

    public function addRecipient( $address = null, $name = null, $type = 'to' ): void
    {
        $recipient = ( $name ) ? $name . ' <' : $address;
        if( $name ){
            $recipient .= $address . '>';
        }
        $this->config['recipients'][$type][] = $recipient;
    }

    public function addAttachment( $file, $name, $type = 'application/octet-stream' ): void
    {
        $this->config['attachments'][] = [
            'file'      => $file,
            'name'      => $name,
            'type'      => $type
        ];
    }

    public function trackOpens( $bool = true ): void
    {
        $this->config['trackOpens'] = ( $bool );
    }

    public function setSender( $address ): void
    {
        $this->config['sender'] = $address;
    }

    public function setReplyTo( $address ): void
    {
        $this->config['replyTo'] = $address;
    }

    public function addData( $data = [] ): void
    {
        $this->config['templateData'] = $data;
    }

    /**
     * @throws PostmarkException
     */
    public function send( $template ): void
    {
        $attachments = [];
        if( $this->config['attachments'] ){
            foreach( $this->config['attachments'] as $attachment ){
                $attachments[] = PostmarkAttachment::fromRawData(
                    $attachment['file'],
                    $attachment['name'],
                    $attachment['type']
                );
            }
        }

        $this->client->sendEmailWithTemplate(
            $this->config['sender'],
            implode( ', ', $this->config['recipients']['to'] ),
            $template,
            $this->config['templateData'],
            $this->config['inlineCss'],
            null, // Tag
            $this->config['trackOpens'],
            $this->config['replyTo'],
            implode( ', ', $this->config['recipients']['cc'] ),
            implode( ', ', $this->config['recipients']['bcc'] ),
            null, // Header array
            ( $attachments ) ? : null,
            null, // Track links
            null, // Metadata array
            null // Message stream
        );
    }
}