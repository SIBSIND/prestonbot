<?php

namespace AppBundle\PullRequests;

use Github\Api\Issue\Comments as CommentsApi;
use AppBundle\Search\Repository as SearchRepository;
use Lpdigital\Github\Entity\Comment;
use Lpdigital\Github\Entity\PullRequest;

/**
 * Get the pull requests according to some filters
 * As GitHub consider pull requests as specific issues
 * don't be surprised too much by the produced repository.
 */
class Repository
{
    private $searchRepository;
    private $commentsApi;

    private $repositoryUsername;
    private $repositoryName;

    public function __construct(
        SearchRepository $searchRepository,
        CommentsApi $commentsApi,
        $repositoryUsername,
        $repositoryName
        ) {
        $this->searchRepository = $searchRepository;
        $this->commentsApi = $commentsApi;
        $this->repositoryUsername = $repositoryUsername;
        $this->repositoryName = $repositoryName;
    }

    public function findAll($base = 'develop')
    {
        $pullRequests = [];
        $search = $this->searchRepository->getPullRequests(['base' => $base]);

        foreach ($search['items'] as $pullRequest) {
            $pullRequests[] = PullRequest::createFromData($pullRequest);
        }

        return $pullRequests;
    }

    public function findAllWithLabel($label, $base = 'develop')
    {
        $pullRequests = [];
        $search = $this->searchRepository->getPullRequests(
            [
                'label' => $this->parseLabel($label),
                'base' => $base,

            ]
        );

        foreach ($search['items'] as $pullRequest) {
            $pullRequests[] = PullRequest::createFromData($pullRequest);
        }

        return $pullRequests;
    }

    public function findAllWaitingSince($nbDays)
    {
        throw new \Exception('Need to be done');

        return [];
    }

    public function getComments(PullRequest $pullRequest)
    {
        $commentsApi = $this->commentsApi
            ->all(
                $this->repositoryUsername,
                $this->repositoryName,
                $pullRequest->getNumber()
            )
        ;

        $comments = [];
        foreach ($commentsApi as $comment) {
            $comments[] = Comment::createFromData($comment);
        }

        return $comments;
    }

    private function parseLabel($label)
    {
        return '"'.$label.'"';
    }
}
