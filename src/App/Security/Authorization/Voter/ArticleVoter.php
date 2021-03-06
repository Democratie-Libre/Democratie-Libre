<?php

namespace App\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use App\Entity\Article;
use App\Entity\User;

class ArticleVoter extends Voter
{
    const EDIT   = 'edit';
    const DELETE = 'delete';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [
            self::EDIT,
            self::DELETE,
        ])) {
            return false;
        }

        if (!$subject instanceof Article) {
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

        $article = $subject;

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($article, $user);
            case self::DELETE:
                return $this->canDelete($article, $user, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit($article, $user)
    {
        // We should have the case of a wiki here
        return $user === $article->getProposal()->getAuthor();
    }

    private function canDelete($article, $user, $token)
    {
        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        return $user === $article->getProposal()->getAuthor();
    }
}
