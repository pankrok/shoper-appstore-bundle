<?php

namespace PanKrok\ShoperAppstoreBundle\Model;

interface ResourceInterface
{
    public function setFilters(array|Filter $filters): static;
    public function getFilters(): array;
    public function setOrder(string $order): static;
    public function getOrder(): array;
    public function setLimit(int $limit): static;
    public function getLimit(): int;
    public function setPage(int $page): static;
    public function getPage(): int;
    public function setOffset(?int $offset): static;
    public function getOffset(): ?int;
    public function setParent(int $parentId): static;
    public function getParent(): ?int;
    public function iterate(): \Generator;
    public function get(array|int|null $body = null): ResponseModel|array;
    public function post(array $body = []): ResponseModel|array;
    public function put(int $id, array $body): ResponseModel|array;
    public function delete(array|int $body): ResponseModel|array;
}
