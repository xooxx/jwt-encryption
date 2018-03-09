<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Jose\Component\Encryption;

use Jose\Component\Core\JWT;

class JWE implements JWT
{
    /**
     * @var Recipient[]
     */
    private $recipients = [];

    /**
     * @var string|null
     */
    private $ciphertext = null;

    /**
     * @var string
     */
    private $iv;

    /**
     * @var string|null
     */
    private $aad = null;

    /**
     * @var string
     */
    private $tag;

    /**
     * @var array
     */
    private $sharedHeader = [];

    /**
     * @var array
     */
    private $sharedProtectedHeader = [];

    /**
     * @var string|null
     */
    private $encodedSharedProtectedHeader = null;

    /**
     * @var string|null
     */
    private $payload = null;

    /**
     * JWE constructor.
     *
     * @param string      $ciphertext
     * @param string      $iv
     * @param string      $tag
     * @param null|string $aad
     * @param array       $sharedHeader
     * @param array       $sharedProtectedHeader
     * @param null|string $encodedSharedProtectedHeader
     * @param array       $recipients
     */
    private function __construct(string $ciphertext, string $iv, string $tag, ?string $aad = null, array $sharedHeader = [], array $sharedProtectedHeader = [], ?string $encodedSharedProtectedHeader = null, array $recipients = [])
    {
        $this->ciphertext = $ciphertext;
        $this->iv = $iv;
        $this->aad = $aad;
        $this->tag = $tag;
        $this->sharedHeader = $sharedHeader;
        $this->sharedProtectedHeader = $sharedProtectedHeader;
        $this->encodedSharedProtectedHeader = $encodedSharedProtectedHeader;
        $this->recipients = $recipients;
    }

    /**
     * Creates a new JWE object.
     *
     * @param string      $ciphertext
     * @param string      $iv
     * @param string      $tag
     * @param null|string $aad
     * @param array       $sharedHeader
     * @param array       $sharedProtectedHeader
     * @param null|string $encodedSharedProtectedHeader
     * @param array       $recipients
     *
     * @return JWE
     */
    public static function create(string $ciphertext, string $iv, string $tag, ?string $aad = null, array $sharedHeader = [], array $sharedProtectedHeader = [], ?string $encodedSharedProtectedHeader = null, array $recipients = []): self
    {
        return new self($ciphertext, $iv, $tag, $aad, $sharedHeader, $sharedProtectedHeader, $encodedSharedProtectedHeader, $recipients);
    }

    /**
     * {@inheritdoc}
     */
    public function getPayload(): ?string
    {
        return $this->payload;
    }

    /**
     * Set the payload.
     * This method is immutable and a new object will be returned.
     *
     * @param string $payload
     *
     * @return JWE
     */
    public function withPayload(string $payload): self
    {
        $clone = clone $this;
        $clone->payload = $payload;

        return $clone;
    }

    /**
     * Returns the number of recipients associated with the JWS.
     *
     * @return int
     */
    public function countRecipients(): int
    {
        return count($this->recipients);
    }

    /**
     * Returns true is the JWE has already been encrypted.
     *
     * @return bool
     */
    public function isEncrypted(): bool
    {
        return null !== $this->getCiphertext();
    }

    /**
     * Returns the recipients associated with the JWS.
     *
     * @return Recipient[]
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * Returns the recipient object at the given index.
     *
     * @param int $id
     *
     * @return Recipient
     */
    public function getRecipient(int $id): Recipient
    {
        if (!array_key_exists($id, $this->recipients)) {
            throw new \InvalidArgumentException('The recipient does not exist.');
        }

        return $this->recipients[$id];
    }

    /**
     * Returns the ciphertext. This method will return null is the JWE has not yet been encrypted.
     *
     * @return string|null The cyphertext
     */
    public function getCiphertext(): ?string
    {
        return $this->ciphertext;
    }

    /**
     * Returns the Additional Authentication Data if available.
     *
     * @return string|null
     */
    public function getAAD(): ?string
    {
        return $this->aad;
    }

    /**
     * Returns the Initialization Vector if available.
     *
     * @return string|null
     */
    public function getIV(): ?string
    {
        return $this->iv;
    }

    /**
     * Returns the tag if available.
     *
     * @return string|null
     */
    public function getTag(): ?string
    {
        return $this->tag;
    }

    /**
     * Returns the encoded shared protected header.
     *
     * @return string
     */
    public function getEncodedSharedProtectedHeader(): string
    {
        return $this->encodedSharedProtectedHeader ?? '';
    }

    /**
     * Returns the shared protected header.
     *
     * @return array
     */
    public function getSharedProtectedHeader(): array
    {
        return $this->sharedProtectedHeader;
    }

    /**
     * Returns the shared protected header parameter identified by the given key.
     * Throws an exception is the the parameter is not available.
     *
     * @param string $key The key
     *
     * @return mixed|null
     */
    public function getSharedProtectedHeaderParameter(string $key)
    {
        if ($this->hasSharedProtectedHeaderParameter($key)) {
            return $this->sharedProtectedHeader[$key];
        }

        throw new \InvalidArgumentException(sprintf('The shared protected header "%s" does not exist.', $key));
    }

    /**
     * Returns true if the shared protected header has the parameter identified by the given key.
     *
     * @param string $key The key
     *
     * @return bool
     */
    public function hasSharedProtectedHeaderParameter(string $key): bool
    {
        return array_key_exists($key, $this->sharedProtectedHeader);
    }

    /**
     * Returns the shared header.
     *
     * @return array
     */
    public function getSharedHeader(): array
    {
        return $this->sharedHeader;
    }

    /**
     * Returns the shared header parameter identified by the given key.
     * Throws an exception is the the parameter is not available.
     *
     * @param string $key The key
     *
     * @return mixed|null
     */
    public function getSharedHeaderParameter(string $key)
    {
        if ($this->hasSharedHeaderParameter($key)) {
            return $this->sharedHeader[$key];
        }

        throw new \InvalidArgumentException(sprintf('The shared header "%s" does not exist.', $key));
    }

    /**
     * Returns true if the shared header has the parameter identified by the given key.
     *
     * @param string $key The key
     *
     * @return bool
     */
    public function hasSharedHeaderParameter(string $key): bool
    {
        return array_key_exists($key, $this->sharedHeader);
    }
}
