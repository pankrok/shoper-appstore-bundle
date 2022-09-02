<?php

namespace PanKrok\ShoperAppstoreBundle\Model;

interface ResourceInterface
{
    public function setFilters(array $filters): ResourceModel;
    public function getFilters(): array;
    public function setOrder(string $order): ResourceModel;
    public function getOrder(): array;
    public function getLimit(): int;
    public function setPage(int $page): ResourceModel;
    public function getPage(): int;
    public function get(array|int|null $body = null): ResponseModel;
    public function post(array $body = []): ResponseModel;
    public function put(int $id, array $body): ResponseModel;
    public function delete(array|int $body): ResponseModel;    
}
