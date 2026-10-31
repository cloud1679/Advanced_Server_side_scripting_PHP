<?php

require_once __DIR__ . '/Database.php';

class LoginAttemptDB
{
    private $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    private function identityHash(
        $accountType,
        $loginId,
        $purpose = 'login'
    ) {
        // Share one counter across both employee login roles.
        $accountGroup = $accountType === 'Customer'
            ? 'Customer'
            : 'Employee';

        return hash(
            'sha256',
            $purpose . "\0"
                . $accountGroup . "\0"
                . strtolower(trim($loginId))
        );
    }

    public function isLocked(
        $accountType,
        $loginId,
        $purpose = 'login'
    ) {
        $statement = $this->connection->prepare(
            'SELECT identity_hash
             FROM login_attempts
             WHERE identity_hash = :identity_hash
               AND locked_until > UTC_TIMESTAMP()'
        );

        $statement->execute([
            'identity_hash' => $this->identityHash(
                $accountType,
                $loginId,
                $purpose
            )
        ]);

        return $statement->fetchColumn() !== false;
    }

    public function recordFailure(
        $accountType,
        $loginId,
        $purpose = 'login'
    ) {
        $identityHash = $this->identityHash(
            $accountType,
            $loginId,
            $purpose
        );

        $this->connection->beginTransaction();

        try {
            $statement = $this->connection->prepare(
                'INSERT INTO login_attempts (
                    identity_hash,
                    failed_attempts,
                    window_started_at,
                    locked_until
                 ) VALUES (
                    :identity_hash,
                    0,
                    UTC_TIMESTAMP(),
                    NULL
                 )
                 ON DUPLICATE KEY UPDATE
                    identity_hash = VALUES(identity_hash)'
            );

            $statement->execute([
                'identity_hash' => $identityHash
            ]);

            $statement = $this->connection->prepare(
                'SELECT
                    failed_attempts,
                    (
                        window_started_at <=
                        DATE_SUB(
                            UTC_TIMESTAMP(),
                            INTERVAL 15 MINUTE
                        )
                    ) AS window_expired,
                    (
                        locked_until > UTC_TIMESTAMP()
                    ) AS currently_locked
                 FROM login_attempts
                 WHERE identity_hash = :identity_hash
                 FOR UPDATE'
            );

            $statement->execute([
                'identity_hash' => $identityHash
            ]);

            $attempt = $statement->fetch(PDO::FETCH_ASSOC);

            if (!$attempt) {
                throw new RuntimeException(
                    'Login attempt tracking is unavailable.'
                );
            }

            if ((int) $attempt['currently_locked'] === 1) {
                $this->connection->commit();
                return;
            }

            $windowExpired =
                (int) $attempt['window_expired'] === 1;

            $failures = $windowExpired
                ? 1
                : (int) $attempt['failed_attempts'] + 1;

            // These SQL fragments are fixed application values.
            $windowSql = $windowExpired
                ? 'UTC_TIMESTAMP()'
                : 'window_started_at';

            $lockSql = $failures >= 5
                ? 'DATE_ADD(UTC_TIMESTAMP(), INTERVAL 15 MINUTE)'
                : 'NULL';

            $statement = $this->connection->prepare(
                'UPDATE login_attempts SET
                    failed_attempts = :failed_attempts,
                    window_started_at = ' . $windowSql . ',
                    locked_until = ' . $lockSql . '
                 WHERE identity_hash = :identity_hash'
            );

            $statement->execute([
                'failed_attempts' => $failures,
                'identity_hash' => $identityHash
            ]);

            $this->connection->commit();
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $exception;
        }
    }

    public function clear(
        $accountType,
        $loginId,
        $purpose = 'login'
    ) {
        $statement = $this->connection->prepare(
            'DELETE FROM login_attempts
             WHERE identity_hash = :identity_hash'
        );

        $statement->execute([
            'identity_hash' => $this->identityHash(
                $accountType,
                $loginId,
                $purpose
            )
        ]);
    }
}
