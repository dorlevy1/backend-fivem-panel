<?php


namespace App\Repositories;

use App\Events\DatabaseChange;
use App\Models\Ban;
use App\Models\Report;
use App\Models\ReportChat;
use App\Models\Warn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportRepository
{

    protected Report $reports;

    public function __construct(Report $report)
    {
        $this->reports = $report;
        $this->notify = new DatabaseChange('reportChatUpdate', 'my-event');
        $this->reportsNotify = new DatabaseChange('reportsUpdate', 'my-event');
        $this->claimNotify = new DatabaseChange('claimChatUpdate', 'my-event');

    }

    public function getReports()
    {
        return Report::with('report_chat')->get();

    }


    public function add($data)
    {

        $report = Report::create([
            'discord'   => strval($data->player['metadata']['discord']),
            'license'   => strval($data->player['license']),
            'name'      => $data->player['name'],
            'reason'    => $data->res['reason'],
            'warned_by' => $data->res['admin']
        ]);

        $report->report_chat->create();

        return $report;
    }

    public function update($data)
    {

        $report = Report::find($data->data['report_id']);
        if ( !$report) {
            return (object)[
                'success' => false,
                'message' => 'Report Not Found'
            ];
        }

        $messages = is_null($report->report_chat->messages) ? [] : [...$report->report_chat->messages];
        $messages[] = [
            'discord_id' => Auth::user()->discord_id,
            'id'         => $data->data['message_id'],
            'message'    => $data->data['message']
        ];

        $report->report_chat->messages = json_encode($messages);
        $report->report_chat->save();
        $data = $report;
        $data['report_chat'] = $report->report_chat;
        $this->notify->setData([
            'report' => $data,
        ]);
        $this->notify->send($this->notify);
        $this->reportsNotify->setData(['reports' => Report::with('report_chat')->get()]);
        $this->reportsNotify->send($this->reportsNotify);

        return (object)[
            'success' => true,
            'data'    => [
                'report'      => $report,
                'report_chat' => $report->report_chat
            ]
        ];
    }

    public function claim($data)
    {

        $report = Report::find($data->data['report_id']);

        if ( !$report) {
            return (object)[
                'success' => false,
                'message' => 'Report Not Found'
            ];
        }
        $report->claim_by = Auth::user()->discord_id;

        $report->save();
        $data = $report;
        $data['report_chat'] = $report->report_chat;
        $this->claimNotify->setData([
            'report' => $data,
        ]);
        $this->claimNotify->send($this->claimNotify);
        $this->reportsNotify->setData(['reports' => Report::with('report_chat')->get()]);
        $this->reportsNotify->send($this->reportsNotify);

        return (object)[
            'success' => true,
        ];

    }

    public function delete($id)
    {
        $ban = Report::find($id);
        if ( !$ban) {
            return (object)[
                'success' => false,
                'message' => 'No Ban Were Found'
            ];
        }
        $ban->delete();

        return (object)[
            'success' => true,
            'message' => 'Ban Deleted Successfully'
        ];
    }

}