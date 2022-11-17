<?php

namespace PanKrok\ShoperAppstoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PanKrok\ShoperAppstoreBundle\Repository\AccessTokensRepository;

/**
 * @ORM\Entity(repositoryClass=AccessTokensRepository::class)
 */
class AccessTokens
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Shops::class, inversedBy="accessTokens")
     */
    private $shop;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expires_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $access_token;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $refresh_token;
    
    public function __toString()
    {
        return $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShop(): ?Shops
    {
        return $this->shop;
    }

    public function setShop(?Shops $shop): self
    {
        $this->shop = $shop;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expires_at;
    }

    public function setExpiresAt(?\DateTimeInterface $expires_at): self
    {
        $this->expires_at = $expires_at;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->access_token;
    }

    public function setAccessToken(?string $access_token): self
    {
        $this->access_token = $access_token;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refresh_token;
    }

    public function setRefreshToken(?string $refresh_token): self
    {
        $this->refresh_token = $refresh_token;

        return $this;
    }
}
