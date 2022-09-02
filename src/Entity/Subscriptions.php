<?php

namespace PanKrok\ShoperAppstoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PanKrok\ShoperAppstoreBundle\Repository\SubscriptionsRepository;

/**
 * @ORM\Entity(repositoryClass=SubscriptionsRepository::class)
 */
class Subscriptions
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Shops::class, inversedBy="subscriptions")
     */
    private $shop;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expires_at;

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
}
