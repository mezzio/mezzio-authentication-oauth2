<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use function array_reduce;
use function trim;

class AbstractRepository
{
    /**
     * @var PdoService
     */
    protected $pdo;

    /**
     * Constructor
     *
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Return a string of scopes, separated by space
     * from ScopeEntityInterface[]
     *
     * @param ScopeEntityInterface[] $scopes
     * @return string
     */
    protected function scopesToString(array $scopes) : string
    {
        if (empty($scopes)) {
            return '';
        }

        return trim(array_reduce($scopes, function ($result, $item) {
            return $result . ' ' . $item->getIdentifier();
        }));
    }
}
