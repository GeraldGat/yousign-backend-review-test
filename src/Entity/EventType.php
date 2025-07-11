<?php

namespace App\Entity;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class EventType extends AbstractEnumType
{
    public const COMMIT_COMMENT = 'CommitCommentEvent';
    public const CREATE = 'CreateEvent';
    public const DELETE = 'DeleteEvent';
    public const FORK = 'ForkEvent';
    public const GOLLUM = 'GollumEvent';
    public const ISSUE_COMMENT = 'IssueCommentEvent';
    public const ISSUES = 'IssuesEvent';
    public const MEMBER = 'MemberEvent';
    public const PUBLIC = 'PublicEvent';
    public const PULL_REQUEST = 'PullRequestEvent';
    public const PULL_REQUEST_REVIEW = 'PullRequestReviewEvent';
    public const PULL_REQUEST_REVIEW_COMMENT = 'PullRequestReviewCommentEvent';
    public const PULL_REQUEST_REVIEW_THREAD = 'PullRequestReviewThreadEvent';
    public const PUSH = 'PushEvent';
    public const RELEASE = 'ReleaseEvent';
    public const SPONSORSHIP = 'SponsorshipEvent';
    public const WATCH = 'WatchEvent';

    protected static array $choices = [
        self::COMMIT_COMMENT => 'Commit comment',
        self::CREATE => 'Create',
        self::DELETE => 'Delete',
        self::FORK => 'Fork',
        self::GOLLUM => 'Gollum',
        self::ISSUE_COMMENT => 'Issue comment',
        self::ISSUES => 'Issues',
        self::MEMBER => 'Member',
        self::PUBLIC => 'Public',
        self::PULL_REQUEST => 'Pull request',
        self::PULL_REQUEST_REVIEW => 'Pull request review',
        self::PULL_REQUEST_REVIEW_COMMENT => 'Pull request review comment',
        self::PULL_REQUEST_REVIEW_THREAD => 'Pull request review thread',
        self::PUSH => 'Push',
        self::RELEASE => 'Release',
        self::SPONSORSHIP => 'Sponsorship',
        self::WATCH => 'Watch'
    ];
}
