<?php

declare(strict_types=1);

namespace PhpMyAdmin\Controllers\Table\Partition;

use PhpMyAdmin\Controllers\Table\AbstractController;
use PhpMyAdmin\Dbal\DatabaseName;
use PhpMyAdmin\Html\Generator;
use PhpMyAdmin\Message;
use PhpMyAdmin\Partitioning\Maintenance;
use PhpMyAdmin\ResponseRenderer;
use PhpMyAdmin\Template;
use Throwable;

use function __;
use function strlen;

final class OptimizeController extends AbstractController
{
    /** @var Maintenance */
    private $model;

    /**
     * @param ResponseRenderer $response
     * @param string           $db
     * @param string           $table
     * @param Maintenance      $maintenance
     */
    public function __construct($response, Template $template, $db, $table, $maintenance)
    {
        parent::__construct($response, $template, $db, $table);
        $this->model = $maintenance;
    }

    public function __invoke(): void
    {
        $partitionName = $_POST['partition_name'] ?? '';

        if (strlen($partitionName) === 0) {
            return;
        }

        try {
            [$rows, $query] = $this->model->optimize(new DatabaseName($this->db), $this->table, $partitionName);
        } catch (Throwable $e) {
            $message = Message::error($e->getMessage());
            $this->response->addHTML($message->getDisplay());

            return;
        }

        $message = Generator::getMessage(
            __('Your SQL query has been executed successfully.'),
            $query,
            'success'
        );

        $this->render('table/partition/optimize', [
            'partition_name' => $partitionName,
            'message' => $message,
            'rows' => $rows,
        ]);
    }
}
