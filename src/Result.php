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

use Drewlabs\Envoyer\Contracts\NotificationResult;
use SendGrid\Response;

class Result implements NotificationResult
{
    /**
     * @var string|\DateTimeInterface
     */
    private $createdAt;

    /**
     * @var string|int
     */
    private $id;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var \Throwable
     */
    private $error;

    /**
     * Creates class instance.
     *
     * @return self
     */
    public function __construct($id = null, int $statusCode = 200, \DateTimeInterface $createdAt = null)
    {
        $this->id = $id;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->statusCode = $statusCode ?? 200;
    }

    /**
     * Creates result instance from sendgrid API response.
     *
     * @return static
     */
    public static function fromResponse(Response $response)
    {
        print_r($response->body());
        // TODO: Query for the id field from the response
        $createdAt = new \DateTimeImmutable();
        $statusCode = $response->statusCode();

        return new static(null, $statusCode, $createdAt);
    }

    /**
     * Create new error result instance.
     *
     * @return static
     */
    public static function exception(\Throwable $exception)
    {
        $instance = new static(null, (int) $exception->getCode(), new \DateTimeImmutable());
        $instance->error = $exception;

        return $instance;
    }

    public function date()
    {
        return $this->createdAt;
    }

    public function id()
    {
        return $this->id;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function isOk()
    {
        return $this->statusCode >= 200 && $this->statusCode <= 204;
    }

    /**
     * Checks if the result has error property set.
     *
     * @return bool
     */
    public function hasError()
    {
        return null !== $this->error;
    }

    /**
     * return the error (exception class) if it's set.
     *
     * @return \Throwable|null
     */
    public function getError()
    {
        return $this->error;
    }
}
