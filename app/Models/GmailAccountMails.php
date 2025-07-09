<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GmailAccountMails extends Model
{
    //
    protected $fillable = [
        'uuid',
        'user_id',
        'gmail_account_id',
        'mail_id',
        'sender',
        'subject',
        'description',
        'received_at',
        'sizeEstimate',
        'label_ids',
    ];

    public static function getInvoiceTotalAfterDate($startDate)
    {
        return DB::table(DB::raw("(
            WITH RECURSIVE invoice_extract AS (
              SELECT 
                uuid,
                gmail_account_id,
                mail_id,
                sender,
                subject,
                description,
                received_at,
                sizeEstimate,
                label_ids,
                created_at,
                updated_at,
                REGEXP_SUBSTR(description, 'R[0-9]+-[0-9]+') AS invoice,
                REGEXP_REPLACE(description, '.*?R[0-9]+-[0-9]+', '') AS rest,
                REGEXP_SUBSTR(description, '\\\\$[0-9]+\\\\.[0-9]{2}') AS amount,
                1 AS level
              FROM gmail_account_mails
              WHERE description REGEXP 'invoice r[0-9]+-[0-9]{4} details'
                AND received_at > '$startDate'

              UNION ALL

              SELECT 
                uuid,
                gmail_account_id,
                mail_id,
                sender,
                subject,
                description,
                received_at,
                sizeEstimate,
                label_ids,
                created_at,
                updated_at,
                REGEXP_SUBSTR(rest, 'R[0-9]+-[0-9]+'),
                REGEXP_REPLACE(rest, '.*?R[0-9]+-[0-9]+', ''),
                REGEXP_SUBSTR(rest, '\\\\$[0-9]+\\\\.[0-9]{2}'),
                level + 1
              FROM invoice_extract
              WHERE rest REGEXP 'R[0-9]+-[0-9]+'
            ),
            ranked_invoices AS (
              SELECT *,
                     ROW_NUMBER() OVER (PARTITION BY invoice ORDER BY received_at DESC) AS rn
              FROM invoice_extract
              WHERE invoice IS NOT NULL
            ),
            final_invoices AS (
              SELECT 
                invoice,
                amount
              FROM ranked_invoices
              WHERE rn = 1 AND amount IS NOT NULL
            )

            SELECT 
              SUM(CAST(REPLACE(amount, '$', '') AS DECIMAL(10,2))) AS total_amount
            FROM final_invoices
        ) as sub"))
            ->value('total_amount');
    }


    public static function getUniqueInvoiceCount($startDate)
    {
        $query = <<<SQL
        WITH RECURSIVE invoice_extract AS (
        SELECT 
            uuid,
            gmail_account_id,
            mail_id,
            sender,
            subject,
            description,
            received_at,
            sizeEstimate,
            label_ids,
            created_at,
            updated_at,
            REGEXP_SUBSTR(description, 'R[0-9]+-[0-9]+') AS invoice,
            REGEXP_REPLACE(description, '.*?R[0-9]+-[0-9]+', '') AS rest,
            1 AS level
        FROM gmail_account_mails
        WHERE description REGEXP 'invoice r[0-9]+-[0-9]{4} details'
            AND received_at > :startDate

        UNION ALL

        SELECT 
            uuid,
            gmail_account_id,
            mail_id,
            sender,
            subject,
            description,
            received_at,
            sizeEstimate,
            label_ids,
            created_at,
            updated_at,
            REGEXP_SUBSTR(rest, 'R[0-9]+-[0-9]+'),
            REGEXP_REPLACE(rest, '.*?R[0-9]+-[0-9]+', ''),
            level + 1
        FROM invoice_extract
        WHERE rest REGEXP 'R[0-9]+-[0-9]+'
        ),
        ranked_invoices AS (
        SELECT *,
                ROW_NUMBER() OVER (PARTITION BY invoice ORDER BY received_at DESC) AS rn
        FROM invoice_extract
        WHERE invoice IS NOT NULL
        )

        SELECT COUNT(*) AS total_unique_invoices
        FROM ranked_invoices
        WHERE rn = 1;
        SQL;

        $result = DB::selectOne($query, ['startDate' => $startDate]);

        return $result->total_unique_invoices ?? 0;
    }
}
