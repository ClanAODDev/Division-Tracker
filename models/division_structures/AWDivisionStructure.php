<?php

/**
 *  Division Structure
 *
 * Generates a bb-code template with prepopulated member data
 *
 */
class AWDivisionStructure
{
    public function __construct($game_id)
    {
        $this->game_id = $game_id;

        // get data
        $this->division = Division::findById($this->game_id);
        $this->platoons = Platoon::find_all($this->game_id);

        // colors
        $this->division_leaders_color = "#00FF00";
        $this->platoon_leaders_color = "#00FF00";
        $this->squad_leaders_color = "#FFA500";
        $this->div_name_color = "#FF0000";
        $this->platoon_num_color = "#FF0000";
        $this->platoon_pos_color = "#40E0D0";

        // number of columns
        $this->num_columns = 4;

        // widths
        $this->players_width = 900;
        $this->info_width = 800;

        // misc settings
        $this->min_num_squad_leaders = 2;

        self::generate();
    }


    public function generate()
    {

        // header
        $division_structure = "[table='align:center,width: {$this->info_width}']";
        $division_structure .= "[tr][td]";
        $division_structure .= "[img]https://s30.postimg.org/wy0yrxa69/Group_shot.png[/img]\r\n";

        /**
         * ------division leaders-----
         */

        $division_structure .= "\r\n\r\n[center][size=5][color={$this->div_name_color}][b][i][u]Division Leaders[/u][/i][/b][/color][/size][/center]\r\n";
        $division_structure .= "[center][size=4]";

        $division_leaders = Division::findDivisionLeaders($this->game_id);
        foreach ($division_leaders as $player) {
            $player_name = Rank::convert($player->rank_id)->abbr . " " . $player->forum_name;
            $aod_url = Member::createAODlink([
                'member_id' => $player->member_id,
                'forum_name' => $player_name,
                'color' => $this->division_leaders_color,
            ]);
            $division_structure .= "{$aod_url} - {$player->position_desc}\r\n";
        }

        $division_structure .= "[/size][/center]\r\n\r\n";

        /**
         * -----general sergeants-----
         */

        $division_structure .= "[center][size=3][color={$this->platoon_pos_color}]General Sergeants[/color]\r\n";
        $general_sergeants = Division::findGeneralSergeants($this->game_id);

        if (count((array) $general_sergeants)) {
            foreach ($general_sergeants as $player) {
                $player_name = Rank::convert($player->rank_id)->abbr . " " . $player->forum_name;
                $aod_url = Member::createAODlink(['member_id' => $player->member_id, 'forum_name' => $player_name]);

                $division_structure .= "{$aod_url}\r\n";
            }
        } else {
            $division_structure .= "No general sergeants assigned";
        }

        $division_structure .= "[/size][/center]";
        $division_structure .= "[/td][/tr][/table]";

        /**
         * ---------platoons----------
         */

        $platoons = $this->platoons;
        $i = 1;

        foreach ($platoons as $platoon) {

            $division_structure .= "[center][size=5]{$platoon->name}[/size]\r\n\r\n";

            // Legion Commander
            $player = Member::findByMemberId($platoon->leader_id);

            // is a Legion Commander assigned?
            if ($platoon->leader_id != 0) {
                $player_name = Rank::convert($player->rank_id)->abbr . " " . $player->forum_name;
                $aod_url = Member::createAODlink([
                    'member_id' => $player->member_id,
                    'forum_name' => $player_name,
                    'color' => $this->platoon_leaders_color,
                ]);
                $division_structure .= "[size=3][color={$this->platoon_pos_color}]Platoon Leader[/color]\r\n{$aod_url}[/size]\r\n\r\n";
            } else {
                $division_structure .= "[size=3][color={$this->platoon_pos_color}]Platoon Leader[/color]\r\n[color={$this->platoon_leaders_color}]TBA[/color][/size]\r\n\r\n";
            }

            $division_structure .= "[/center]";

            // Regimental Leaders
            $squads = Squad::findAll($this->game_id, $platoon->id);

            $division_structure .= "\r\n\r\n[table='align:center,class:grid,,width:900']";
            $division_structure .= "[tr]";

            foreach ($squads as $squad) {
                $division_structure .= "[td][center]";

                if ($squad->leader_id != 0) {
                    $squad_leader = Member::findById($squad->leader_id);
                    $player_name = Rank::convert($squad_leader->rank_id)->abbr . " " . $squad_leader->forum_name;
                    $aod_url = Member::createAODlink([
                        'member_id' => $squad_leader->member_id,
                        'forum_name' => $player_name,
                        'color' => $this->squad_leaders_color,
                    ]);

                    $division_structure .= "[size=3][color={$this->platoon_pos_color}]Squad Leader[/color]\r\n{$aod_url}[/size]\r\n\r\n";

                    // direct recruits
                    $recruits = arrayToObject(Member::findRecruits($squad_leader->member_id, $squad_leader->platoon_id,
                        $squad->id, true));

                    if (count((array) $recruits)) {
                        foreach ($recruits as $player) {
                            $player_name = Rank::convert($player->rank_id)->abbr . " " . $player->forum_name;
                            $aod_url = Member::createAODlink([
                                'member_id' => $player->member_id,
                                'forum_name' => $player_name,
                            ]);

                            $division_structure .= "*{$aod_url}\r\n";
                        }
                    }
                } else {
                    $division_structure .= "[size=3][color={$this->platoon_pos_color}]Squad Leader[/color]\r\n[color={$this->squad_leaders_color}]TBA[/color][/size]\r\n";

                }

                $division_structure .= "\r\n";

                // squad members
                $squadMembers = arrayToObject(
                    Squad::findSquadMembers(
                        $squad->id,
                        true,
                        (isset($squad_leader)) ? $squad_leader->member_id : null
                    )
                );

                if (count((array) $squadMembers)) {
                    foreach ($squadMembers as $player) {
                        $player_name = Rank::convert($player->rank_id)->abbr . " " . $player->forum_name;
                        $aod_url = Member::createAODlink([
                            'member_id' => $player->member_id,
                            'forum_name' => $player_name,
                        ]);
                        $division_structure .= "{$aod_url}\r\n";
                    }
                }

                $division_structure .= "[/center][/td]";
            }

            $division_structure .= "[/tr][/table]";
            $division_structure .= "\r\n\r\n";

            $i++;
        }

        /**
         * --------part timers--------
         */

        $partTimers = PartTime::find_all($this->game_id);

        if (count($partTimers)) {
            $i = 1;

            // header
            $division_structure .= "\r\n[table='align:center,width: {$this->info_width}']";
            $division_structure .= "[tr][td]\r\n[center][size=3][color={$this->platoon_pos_color}][b]Part Time Members[/b][/color][/size][/center][/td][/tr]";
            $division_structure .= "[/table]\r\n\r\n";

            // players
            $division_structure .= "[table='align:center,width: {$this->info_width}']";
            $division_structure .= "[tr][td]";

            foreach ($partTimers as $player) {
                if ($i % 20 == 0) {
                    $division_structure .= "[/td][td]";
                }
                $aod_url = Member::createAODlink([
                    'member_id' => $player->member_id,
                    'forum_name' => "AOD_" . $player->forum_name,
                ]);
                $division_structure .= "{$aod_url}\r\n";
                $i++;
            }

            $division_structure .= "[/td]";
            $division_structure .= "[/tr][/table]\r\n\r\n";

        }

        /**
         * -----------LOAS------------
         */


        if (count((array) LeaveOfAbsence::find_all($this->game_id))) {
            $i = 1;

            // header
            $division_structure .= "\r\n[table='align:center,width: {$this->info_width}']";
            $division_structure .= "[tr][td]\r\n[center][size=3][color={$this->platoon_pos_color}][b]Leaves of Absence[/b][/color][/size][/center][/td][/tr]";
            $division_structure .= "[/table]\r\n\r\n";

            // players
            $division_structure .= "[table='align:center,width: {$this->info_width}']";
            $loas = LeaveOfAbsence::find_all($this->game_id);

            foreach ($loas as $player) {
                $date_end = (strtotime($player->date_end) < strtotime('now'))
                    ? "[COLOR='#FF0000']Expired " . formatTime(strtotime($player->date_end)) . "[/COLOR]"
                    : date("M d, Y",
                        strtotime($player->date_end));
                $profile = Member::findByMemberId($player->member_id);
                $aod_url = Member::createAODlink([
                    'member_id' => $player->member_id,
                    'forum_name' => "AOD_" . $profile->forum_name,
                ]);

                $division_structure .= "[tr][td]{$aod_url}[/td][td]{$date_end}[/td][td]{$player->reason}[/td][/tr]";
                $i++;
            }

            $division_structure .= "[/table]";
        }

        $this->content = $division_structure;
    }
}
