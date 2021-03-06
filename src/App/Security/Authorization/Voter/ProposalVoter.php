<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use App\Entity\Proposal;
use App\Entity\User;

class ProposalVoter extends Voter
{
    const EDIT       = 'edit';
    const AUTHOR     = 'author';
    const SUPPORTER  = 'supporter';
    const OPPONENT   = 'opponent';
    const NEUTRAL    = 'neutral';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [
            self::EDIT,
            self::AUTHOR,
            self::SUPPORTER,
            self::OPPONENT,
            self::NEUTRAL
        ])) {
            return false;
        }

        if (!$subject instanceof Proposal) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $proposal = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($proposal, $user, $token);
            case self::AUTHOR:
                return $this->isAuthor($proposal, $user);
            case self::SUPPORTER:
                return $this->isSupporter($proposal, $user);
            case self::OPPONENT:
                return $this->isOpponent($proposal, $user);
            case self::NEUTRAL:
                return $this->isNeutral($proposal, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit($proposal, $user, $token)
    {
        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        if ($this->isAuthor($proposal, $user)) {
            return true;
        }

        return $proposal->isAWiki();
    }

    private function isAuthor($proposal, $user)
    {
        return $user === $proposal->getAuthor();
    }

    private function isSupporter($proposal, $user)
    {
        return $proposal->getSupporters()->contains($user);
    }

    private function isOpponent($proposal, $user)
    {
        return $proposal->getOpponents()->contains($user);
    }

    private function isNeutral($proposal, $user)
    {
        return !$this->isSupporter($proposal, $user) and !$this->isOpponent($proposal, $user);
    }
}
