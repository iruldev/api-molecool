<?php

namespace App\Console\Commands;

use App\Models\Comment;
use Illuminate\Console\Command;


class commentsQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:comments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue Comments';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!app('redis')->exists('comments')) {
            $this->error('Redis Comments Not Found!');
        }

        if (!empty(app('redis')->get('comments'))) {
            $comments = app('redis')->get('comments');
            $comments = json_decode($comments, true);

            foreach ($comments as $comment) {
                Comment::create($comment);
            }

            app('redis')->set('comments', '');

            $this->info("All Comments Have Been Imported to DB");
        } else {
            $this->error('Redis Comments Empty');
        }
    }
}
