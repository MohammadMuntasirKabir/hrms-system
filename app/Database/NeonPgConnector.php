<?php

namespace App\Database;

use Illuminate\Database\Connectors\PostgresConnector;

/**
 * Postgres connector that appends Neon's required "endpoint" option to the DSN.
 *
 * Neon's server (behind a proxy) needs the endpoint ID passed via the libpq
 * `options=endpoint=<id>` connection keyword when the client library lacks
 * SNI support (e.g. Vercel's bundled libpq). Laravel builds the pgsql DSN
 * manually and does not forward the URL `options` query param into the DSN,
 * so we inject it here, sourced from NEON_ENDPOINT_ID.
 */
class NeonPgConnector extends PostgresConnector
{
    protected function getDsn(array $config)
    {
        $dsn = parent::getDsn($config);

        if ($endpoint = env('NEON_ENDPOINT_ID')) {
            $dsn .= ';options=endpoint='.$endpoint;
        }

        return $dsn;
    }
}
