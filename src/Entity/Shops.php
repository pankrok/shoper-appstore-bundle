<?php

namespace PanKrok\ShoperAppstoreBundle\Entity;

use PanKrok\ShoperAppstoreBundle\Repository\ShopsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShopsRepository::class)]
class Shops
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shop = null;

    #[ORM\Column(length: 512, nullable: true)]
    private ?string $shop_url = null;

    #[ORM\Column(length: 11)]
    private ?string $version = null;

    #[ORM\Column(nullable: true)]
    private ?bool $installed = null;

    #[ORM\OneToOne(mappedBy: 'shop', cascade: ['persist', 'remove'])]
    private ?AccessTokens $accessTokens = null;

    /**
     * @var Collection<int, Billings>
     */
    #[ORM\OneToMany(targetEntity: Billings::class, mappedBy: 'shop')]
    private Collection $billings;

    /**
     * @var Collection<int, Subscriptions>
     */
    #[ORM\OneToMany(targetEntity: Subscriptions::class, mappedBy: 'shop')]
    private Collection $subscriptions;

    public function __construct()
    {
        $this->billings = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->shop;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getShop(): ?string
    {
        return $this->shop;
    }

    public function setShop(?string $shop): static
    {
        $this->shop = $shop;

        return $this;
    }

    public function getShopUrl(): ?string
    {
        return $this->shop_url;
    }

    public function setShopUrl(?string $shop_url): static
    {
        $this->shop_url = $shop_url;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function isInstalled(): ?bool
    {
        return $this->installed;
    }

    public function setInstalled(?bool $installed): static
    {
        $this->installed = $installed;

        return $this;
    }

    public function getAccessTokens(): ?AccessTokens
    {
        return $this->accessTokens;
    }

    public function setAccessTokens(?AccessTokens $accessTokens): static
    {
        // unset the owning side of the relation if necessary
        if (null === $accessTokens && null !== $this->accessTokens) {
            $this->accessTokens->setShop(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $accessTokens && $accessTokens->getShop() !== $this) {
            $accessTokens->setShop($this);
        }
        
        $this->accessTokens = $accessTokens;

        return $this;
    }

    /**
     * @return Collection<int, Billings>
     */
    public function getBillings(): Collection
    {
        return $this->billings;
    }

    public function addBilling(Billings $billing): static
    {
        if (!$this->billings->contains($billing)) {
            $this->billings->add($billing);
            $billing->setShop($this);
        }

        return $this;
    }

    public function removeBilling(Billings $billing): static
    {
        if ($this->billings->removeElement($billing)) {
            // set the owning side to null (unless already changed)
            if ($billing->getShop() === $this) {
                $billing->setShop(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Subscriptions>
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscriptions $subscription): static
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions->add($subscription);
            $subscription->setShop($this);
        }

        return $this;
    }

    public function removeSubscription(Subscriptions $subscription): static
    {
        if ($this->subscriptions->removeElement($subscription)) {
            // set the owning side to null (unless already changed)
            if ($subscription->getShop() === $this) {
                $subscription->setShop(null);
            }
        }

        return $this;
    }
}
