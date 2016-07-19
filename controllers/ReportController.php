<?php

class ReportController
{

    public static function _retentionNumbers()
    {
        $user = User::find(intval($_SESSION['userid']));

        if ($user->rank >= 9 || User::isDev()) {
            $member = Member::find(intval($_SESSION['memberid']));
            $tools = Tool::find_all($user->role);
            $divisions = Division::find_all();
            $recruited = Report::recruitedLast30days($member->game_id);
            $removed = Report::removedLast30days($member->game_id);
            $monthlyBreakdown = Report::recruitingWeekly($member->game_id);
            $byTheMonth = Report::recruitingByTheMonth($member->game_id);
            $js = 'report';
            Flight::render('reports/retention', compact('recruited', 'removed', 'js', 'monthlyBreakdown', 'byTheMonth'),
                'content');
            Flight::render('layouts/application',
                array('user' => $user, 'member' => $member, 'tools' => $tools, 'divisions' => $divisions));
        } else {
            Flight::redirect('/404', 404);
        }
    }

    public static function _getPromotions()
    {
        $user = User::find(intval($_SESSION['userid']));
        $member = Member::find(intval($_SESSION['memberid']));
        $tools = Tool::find_all($user->role);
        $divisions = Division::find_all();
        $division = Division::find($member->game_id);
        $js = 'report';
        if ($division instanceof Division) {
            $promotions = Division::getPromotionsThisMonth($division->id);
            Flight::render('reports/promotions', compact('division', 'promotions'), 'content');
            Flight::render('layouts/application', compact('user', 'member', 'tools', 'divisions', 'js'));
        }
    }

    public static function _getPromotionsLastMonth()
    {
        $user = User::find(intval($_SESSION['userid']));
        $member = Member::find(intval($_SESSION['memberid']));
        $tools = Tool::find_all($user->role);
        $divisions = Division::find_all();
        $division = Division::find($member->game_id);
        $js = 'report';
        if ($division instanceof Division) {
            $promotions = Division::getPromotionsLastMonth($division->id);
            Flight::render('reports/promotionsLastMonth', compact('division', 'promotions'), 'content');
            Flight::render('layouts/application', compact('user', 'member', 'tools', 'divisions', 'js'));
        }
    }

}
