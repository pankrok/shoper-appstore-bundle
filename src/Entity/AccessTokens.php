<?php

namespace PanKrok\ShoperAppstoreBundle\Entity;

use PanKrok\ShoperAppstoreBundle\Repository\AccessTokensRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccessTokensRepository::class)]
class AccessTokens
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $expires_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(length: 64)]
    private ?string $access_token = null;

    #[ORM\Column(length: 64)]
    private ?string $refresh_token = null;

    #[ORM\OneToOne(mappedBy: 'accessTokens', cascade: ['persist', 'remove'])]
    private ?Shops $shop = null;

    public function __toString()
    {
        return $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expires_at;
    }

    public function setExpiresAt(?\DateTimeImmutable $expires_at): static
    {
        $this->expires_at = $expires_at;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->access_token;
    }

    public function setAccessToken(string $access_token): static
    {
        $this->access_token = $access_token;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refresh_token;
    }

    public function setRefreshToken(string $refresh_token): static
    {
        $this->refresh_token = $refresh_token;

        return $this;
    }

    public function getShop(): ?Shops
    {
        return $this->shop;
    }

    public function setShop(?Shops $shop): static
    {
        // unset the owning side of the relation if necessary
        if ($shop === null && $this->shop !== null) {
            $this->shop->setAccessTokens(null);
        }

        // set the owning side of the relation if necessary
        if ($shop !== null && $shop->getAccessTokens() !== $this) {
            $shop->setAccessTokens($this);
        }

        $this->shop = $shop;

        return $this;
    }
}
