<?php

namespace BNETDocs\Controllers\Analytics;

use \BNETDocs\Libraries\Core\HttpCode;
use \DateTimeImmutable;
use \DateTimeInterface;
use \PDOStatement;

class Dashboard extends \BNETDocs\Controllers\Base
{
    public function __construct()
    {
        $this->model = new \BNETDocs\Models\Analytics\Dashboard();
    }

    public function invoke(?array $args): bool
    {
        $this->model->acl_allowed = ($this->model->active_user && $this->model->active_user->getOption(
            \BNETDocs\Libraries\User\User::OPTION_ACL_ANALYTICS_VIEW
        ));

        if (!$this->model->acl_allowed)
        {
            $this->model->_responseCode = HttpCode::HTTP_FORBIDDEN;
            return true;
        }

        $this->handleDateRange();
        $this->getAnalytics();
        $this->model->_responseCode = HttpCode::HTTP_OK;
        return true;
    }

    private function getAnalytics(): void
    {
        $tables = [
            'comments' => 'count_comments',
            'documents' => 'count_documents',
            'event_log' => 'count_event_log',
            'news_posts' => 'count_news_posts',
            'packets' => 'count_packets',
            'servers' => 'count_servers',
            'tags' => 'count_tags',
            'user_profiles' => 'count_user_profiles',
            'users' => 'count_users',
        ];

        foreach ($tables as $table => $property)
        {
            $this->model->$property = $this->countTableRows($table);
        }

        $start = $this->model->date_start;
        $end = $this->model->date_end;

        if ($start && $end)
        {
            $timestamp_columns = [
                'comments' => 'created_datetime',
                'documents' => 'created_datetime',
                'event_log' => 'event_datetime',
                'news_posts' => 'created_datetime',
                'packets' => 'created_datetime',
                'servers' => 'created_datetime',
                'users' => 'created_datetime',
                // excluded: 'tags', 'user_profiles'
            ];

            foreach ($timestamp_columns as $table => $timestamp_column)
            {
                $property = 'interval_new_' . $table;
                $this->model->$property = $this->countNewRowsInRange($table, $timestamp_column, $start, $end);
            }
        }
    }

    private function countNewRowsInRange(
        string $table, string $timestamp_column, DateTimeInterface $start, DateTimeInterface $end
    ): int
    {
        $pdo = \BNETDocs\Libraries\Db\MariaDb::instance();
        $count = 0;

        $query = "SELECT COUNT(*) FROM `{$table}` WHERE `{$timestamp_column}` BETWEEN :start AND :end;";

        $params = [
            ':start' => $start->format('Y-m-d 00:00:00'),
            ':end' => $end->format('Y-m-d 23:59:59'),
        ];

        try
        {
            $stmt = $pdo->prepare($query);
            if ($stmt && $stmt->execute($params) && $stmt->rowCount() === 1)
            {
                $count = (int) $stmt->fetchColumn();
            }
        }
        finally
        {
            if (isset($stmt) && $stmt instanceof PDOStatement) $stmt->closeCursor();
        }

        return $count;
    }

    private function countTableRows(string $table): int
    {
        $pdo = \BNETDocs\Libraries\Db\MariaDb::instance();
        $count = 0;

        try
        {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$table}`;");
            if ($stmt && $stmt->execute() && $stmt->rowCount() === 1)
            {
                $count = (int) $stmt->fetchColumn();
            }
        }
        finally
        {
            if (isset($stmt) && $stmt instanceof PDOStatement) $stmt->closeCursor();
        }

        return $count;
    }

    private function handleDateRange(): void
    {
        $data = \BNETDocs\Libraries\Core\Router::query();

        if (isset($data['date_start']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_start']))
        {
            $this->model->date_start = new DateTimeImmutable($data['date_start']);
        }

        if (isset($data['date_end']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_end']))
        {
            $this->model->date_end = new DateTimeImmutable($data['date_end']);
        }

        // Fallback: if only one is set, assume the other is today
        if (!$this->model->date_end)
        {
            $this->model->date_end = new DateTimeImmutable('today');
        }

        if (!$this->model->date_start && $this->model->date_end)
        {
            // Default to 30-day window
            $this->model->date_start = $this->model->date_end->sub(new \DateInterval('P30D'));
        }
    }
}
