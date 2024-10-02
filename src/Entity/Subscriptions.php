<?php

namespace PanKrok\ShoperAppstoreBundle\Entity;

use PanKrok\ShoperAppstoreBundle\Repository\SubscriptionsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionsRepository::class)]
class Subscriptions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $expires_at = null;

    #[ORM\ManyToOne(inversedBy: 'subscriptions')]
    private ?Shops $shop = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getShop(): ?Shops
    {
        return $this->shop;
    }

    public function setShop(?Shops $shop): static
    {
        $this->shop = $shop;

        return $this;
    }
}
