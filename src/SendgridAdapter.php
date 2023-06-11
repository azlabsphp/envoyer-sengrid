<?php

declare(strict_types=1);

/*
 * This file is part of the drewlabs namespace.
 *
 * (c) Sidoine Azandrew <azandrewdevelopper@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Drewlabs\Envoyer\Drivers\Sendgrid;

use Drewlabs\Envoyer\Contracts\ApiKeyAware;
use Drewlabs\Envoyer\Contracts\AttachedAddressesAware;
use Drewlabs\Envoyer\Contracts\AttachmentsAware;
use Drewlabs\Envoyer\Contracts\ClientInterface;
use Drewlabs\Envoyer\Contracts\NotificationInterface;
use Drewlabs\Envoyer\Contracts\NotificationResult;
use Drewlabs\Envoyer\Contracts\SubjectAware;
use Psr\Http\Message\StreamInterface;
use SendGrid\Mail\Attachment;

class SendgridAdapter implements ClientInterface
{
    /**
     * @var ApiKeyAware
     */
    private $server;

    /**
     * Creates class instance.
     */
    public function __construct(ApiKeyAware $server)
    {
        $this->server = $server;
    }

    /**
     * Creates new class instance.
     *
     * @return static
     */
    public static function new(string $apiKey)
    {
        return new static(new ApiKeyServer($apiKey));
    }

    public function sendRequest(NotificationInterface $instance): NotificationResult
    {
        try {
            // Creates sendgrid mail instance
            $mail = new \SendGrid\Mail\Mail();

            // Buil sendgrid mail
            $mail->setFrom($instance->getSender()->__toString(), $instance->getSender()->name());
            $mail->addTo($instance->getReceiver()->__toString(), $instance->getReceiver()->name());
            // Add contents
            $mail->addContent('text/html', (string) $instance->getContent());

            // Add subject
            if ($instance instanceof SubjectAware) {
                $mail->setSubject($instance->getSubject());
            } else {
                $mail->setSubject('No Subject.');
            }

            // Add attached addresses
            if ($instance instanceof AttachedAddressesAware) {
                $addresses = array_filter(
                    $instance->getAttachedAddresses(),
                    static function ($value) {
                        return !empty($value);
                    }
                );
                if ((null !== $addresses) && (!empty($addresses))) {
                    $mail->addBccs($addresses);
                }
            }

            // Add mail attachments
            if ($instance instanceof AttachmentsAware) {
                foreach ($this->getAttachments($instance) as $attachment) {
                    $mail->addAttachment($attachment);
                }
            }

            // Send email using Sendrid API
            return Result::fromResponse((new \SendGrid($this->server->getApiKey()))->send($mail));
        } catch (\Exception $e) {
            return Result::exception($e);
        }
    }

    /**
     * Get list of mail attachements.
     *
     * @throws \RuntimeException
     *
     * @return \Generator<int, Attachment, mixed, void>
     */
    private function getAttachments(AttachmentsAware $instance)
    {
        foreach ($instance->getAttachments() as $attachment) {
            if ($attachment instanceof StreamInterface) {
                yield new Attachment(base64_encode($attachment->getContents()), $this->getStreamMimeType($attachment));
            } elseif ($attachment instanceof \SplFileInfo && $attachment->isReadable()) {
                yield new Attachment(base64_encode(file_get_contents($attachment->getRealPath())), $this->getFinfoMimeType($attachment), $attachment->getFilename());
            } else {
                yield new Attachment(base64_encode($attachment));
            }
        }
    }

    /**
     * @return string|false|null
     */
    private function getStreamMimeType(StreamInterface $stream)
    {
        if (\function_exists('mime_content_type')) {
            $tmp = $stream->getMetadata('uri');

            return mime_content_type($tmp) ?? null;
        }

        return null;
    }

    /**
     * @return string|false|null
     */
    private function getFinfoMimeType(\SplFileInfo $info)
    {
        if (\function_exists('finfo_file') && \function_exists('finfo_open') && \function_exists('finfo_close')) {
            $finfo = finfo_open();
            $info = finfo_file($finfo, $info->getRealPath(), \FILEINFO_MIME_TYPE);
            finfo_close($finfo);

            return $info;
        }

        return null;
    }
}
