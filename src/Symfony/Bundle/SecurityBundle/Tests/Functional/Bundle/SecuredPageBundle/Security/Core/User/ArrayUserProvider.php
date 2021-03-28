<?php

namespace Symfony\Bundle\SecurityBundle\Tests\Functional\Bundle\SecuredPageBundle\Security\Core\User;

use Symfony\Bundle\SecurityBundle\Tests\Functional\UserWithoutEquatable;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ArrayUserProvider implements UserProviderInterface
{
    /** @var UserInterface[] */
    private $users = [];

    public function addUser(UserInterface $user)
    {
        $this->users[$user->getUserIdentifier()] = $user;
    }

    public function setUser($username, UserInterface $user)
    {
        $this->users[$username] = $user;
    }

    public function getUser($username)
    {
        return $this->users[$username];
    }

    public function loadUserByUsername($username)
    {
        return $this->loadUserByIdentifier($username);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->getUser($identifier);

        if (null === $user) {
            $e = new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
            $e->setUsername($identifier);

            throw $e;
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof UserInterface) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_debug_type($user)));
        }

        $storedUser = $this->getUser($user->getUserIdentifier());
        $class = \get_class($storedUser);

        return new $class($storedUser->getUserIdentifier(), $storedUser->getPassword(), $storedUser->getRoles(), $storedUser->isEnabled());
    }

    public function supportsClass($class)
    {
        return InMemoryUser::class === $class || UserWithoutEquatable::class === $class;
    }
}
