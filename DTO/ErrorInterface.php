<?php

namespace Classified\ErrorHandlingBundle\DTO;

use Classified\ErrorHandling\Entity\ErrorCategory;
use Classified\ErrorHandling\Exception\CreateAccountAdapterException;
use Classified\WebsiteBundle\Adapter\Account\AbstractAdapter;
use Classified\WebsiteBundle\Entity\Account;
use Classified\WebsiteBundle\Entity\Organization;
use Classified\WebsiteBundle\Provider\AccountAdapterProviderInterface;

interface ErrorInterface
{
    /**
     * Assign array data to correspondent DTO properties.
     *
     * @param array $data
     */
    public function load(array $data): void;

    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @param string $message
     *
     * @return ErrorInterface
     */
    public function setMessage(string $message): ErrorInterface;

    /**
     * @return ErrorCategory
     */
    public function getCategory(): ErrorCategory;

    /**
     * @param ErrorCategory $errorCategory
     *
     * @return ErrorInterface
     */
    public function setCategory(ErrorCategory $errorCategory): ErrorInterface;

    /**
     * Returns account number.
     *
     * @return string|null Account number
     */
    public function getAccountNumber(): ?string;

    /**
     * Sets account number.
     *
     * @param string $accountNumber Account number
     * @return $this
     */
    public function setAccountNumber(string $accountNumber): ErrorInterface;

    /**
     * Returns account.
     *
     * @return Account|null Account
     */
    public function getAccount(): ?Account;

    /**
     * Sets account.
     *
     * @param Account $account Account
     * @return $this
     */
    public function setAccount(Account $account): ErrorInterface;

    /**
     * @return Organization
     */
    public function getOrganization(): Organization;

    /**
     * @param Organization $organization
     *
     * @return ErrorInterface
     */
    public function setOrganization(Organization $organization): ErrorInterface;

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime;
}
