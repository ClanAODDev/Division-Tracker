<?php

class PUBGDivisionStructure
{
    private $banner;

    public function __construct($game_id)
    {
        $this->banner = "http://i.imgur.com/vAUyUy7.png";
        $this->game_id = $game_id;

        // get data
        $this->division = Division::findById($this->game_id);
        $this->platoons = Platoon::find_all($this->game_id);

        // colors
        $this->division_leaders_color = "#FF0000";
        $this->general_sergeants_color = "#00FFFF";
        $this->platoon_name_color = "#00FFFF";
        $this->platoon_leader_color = "#FFA07A";
        $this->squad_leader_color = "#00FFFF";

        // number of columns
        $this->num_columns_squads = 2;

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
        $division_structure = "[center]";

        // banner
        $division_structure .= "[img]{$this->banner}[/img]\r\n";

        // division leaders
        $division_structure .= "\r\n\r\n[size=5][color={$this->division_leaders_color}][b][i][u]Division Leadership[/u][/i][/b][/color][/size]\r\n";
        $division_structure .= "[size=4]";
        $division_structure = $this->getDivisionLeaders($division_structure);
        $division_structure .= "[/size][/center]\r\n\r\n";

        // general sergeants
        $division_structure = $this->getGeneralSergeants($division_structure);

        // groups
        $division_structure = $this->getGroups($division_structure);

        // LOAs
        $division_structure = $this->getLoas($division_structure);

        // populate content
        $this->content = $division_structure;
    }

    /**
     * @param $division_structure
     * @return string
     */
    private function getDivisionLeaders($division_structure)
    {
        $division_leaders = Division::findDivisionLeaders($this->game_id);
        foreach ($division_leaders as $division_leader) {
            $aod_url = Member::createAODlink([
                'member_id' => $division_leader->member_id,
                'rank' => Rank::convert($division_leader->rank_id)->abbr,
                'forum_name' => $division_leader->forum_name,
            ]);
            $division_structure .= (property_exists($division_leader,
                'position_desc')) ? "{$aod_url} - {$division_leader->position_desc}\r\n" : "{$aod_url}\r\n";
        }

        return $division_structure;
    }

    /**
     * @param $division_structure
     * @return string
     */
    private function getGeneralSergeants($division_structure)
    {
        $general_sergeants = Division::findGeneralSergeants($this->game_id);

        if (count((array) $general_sergeants)) {
            $division_structure .= "[size=3][color={$this->general_sergeants_color}]General Sergeants[/color]\r\n";
            foreach ($general_sergeants as $general_sergeant) {
                $aod_url = Member::createAODlink([
                    'member_id' => $general_sergeant->member_id,
                    'rank' => Rank::convert($general_sergeant->rank_id)->abbr,
                    'forum_name' => $general_sergeant->forum_name,
                ]);
                $division_structure .= "{$aod_url}\r\n";
            }

            $division_structure .= "[/size]";
        }

        return $division_structure;
    }

    /**
     * @param $division_structure
     * @return string
     */
    private function getGroups($division_structure)
    {
        foreach ($this->platoons as $platoon) {
            $division_structure .= "[table=align:center][tr][td][center]";

            // group leader
            $division_structure .= "[size=4][color={$this->platoon_name_color}]{$platoon->name}[/color][/size]\r\n\r\n";

            $group_leader = Member::findByMemberId($platoon->leader_id);
            $memberHandle = MemberHandle::findHandle($player->id, $this->division->primary_handle);
            $group_leader->handle = (is_object($memberHandle))
                ? "[color=#00FFFF][url=http://steamcommunity.com/profiles/{$memberHandle->handle_value}][Steam][/url][/color]"
                : 'XXX';

            // is a group leader assigned?
            if ($platoon->leader_id != 0) {
                $aod_url = Member::createAODlink(array(
                    'member_id' => $group_leader->member_id,
                    'forum_name' => Rank::convert($group_leader->rank_id)->abbr . " " . $group_leader->forum_name,
                    'color' => $this->platoon_leader_color
                ));
                $division_structure .= "[size=4]{$aod_url}[/size]\r\n\r\n";

            } else {
                $division_structure .= "[size=4]TBA[/size]\r\n\r\n";
            }
            $division_structure .= "[/center][/td][/tr][/table]";

            /**
             * Squads
             */
            $division_structure .= "[table=align:center][tr]";
            $division_structure = $this->getSquads($division_structure, $platoon);
            $division_structure .= "[/tr][/table]";

            $division_structure .= "\r\n\r\n\r\n\r\n\r\n\r\n\r\n";
        }

        return $division_structure;
    }

    /**
     * @param $division_structure
     * @param $platoon
     * @return string
     */
    private function getSquads($division_structure, $platoon)
    {
        $squads = Squad::findAll($this->game_id, $platoon->id);
        $iterate_squad = 0;

        foreach ($squads as $squad) {
            $division_structure .= "[td]";
            // squad leader
            if ($squad->leader_id != 0) {
                $squad_leader = Member::findById($squad->leader_id);

                $aod_url = Member::createAODlink([
                    'member_id' => $squad_leader->member_id,
                    'forum_name' => Rank::convert($squad_leader->rank_id)->abbr . " " . ucfirst($squad_leader->forum_name),
                    'color' => $this->squad_leader_color
                ]);

                $division_structure .= "[size=4]{$aod_url}[/size]\r\n\r\n";

                $recruits = arrayToObject(Member::findRecruits($squad_leader->member_id, $squad_leader->platoon_id,
                    false, true));
                $division_structure .= "[list=1]";
                foreach ($recruits as $player) {
                    $memberHandle = MemberHandle::findHandle($player->id, $this->division->primary_handle);
                    $player->handle = (is_object($memberHandle))
                        ? "[url={$memberHandle->handle_value}][color=#00FFFF][Steam][/color][/url]"
                        : 'XXX';

                    $aod_url = Member::createAODlink([
                        'member_id' => $player->member_id,
                        'forum_name' => Rank::convert($player->rank_id)->abbr . " " . $player->forum_name,
                    ]);
                    $division_structure .= "[*]{$aod_url} {$player->handle}\r\n\r\n";
                }
                $division_structure .= "[/list]\r\n";

            } else {
                $division_structure .= "[size=4][color={$this->squad_leader_color}]TBA[/color][/size]\r\n\r\n";
            }
            // end squad leader
            // squad members

            $squadMembers = arrayToObject(
                Squad::findSquadMembers(
                    $squad->id,
                    true,
                    (isset($squad_leader)) ? $squad_leader->member_id : null
                )
            );

            if (count((array) $squadMembers)) {
                $division_structure .= "[list]";
                foreach ($squadMembers as $player) {

                    $memberHandle = MemberHandle::findHandle($player->id, $this->division->primary_handle);
                    $player->handle = (is_object($memberHandle))
                        ? "[url={$memberHandle->handle_value}][color=#00FFFF][Steam][/color][/url]"
                        : 'XXX';

                    $player_name = Rank::convert($player->rank_id)->abbr . " " . $player->forum_name;
                    $aod_url = Member::createAODlink(array(
                        'member_id' => $player->member_id,
                        'forum_name' => $player_name
                    ));
                    $division_structure .= "[*]{$aod_url} {$player->handle}\r\n";
                }
                $division_structure .= "[/list]";
            }
            // end squad members
            $division_structure .= "[/td]";
            $iterate_squad++;

            if ($iterate_squad % 2 == 0) {
                $division_structure .= "[/tr][tr]";
            }
        }

        return $division_structure;
    }

    /**
     * @param $division_structure
     * @return string
     */
    private function getLoas($division_structure)
    {
        if (count((array) LeaveOfAbsence::find_all($this->game_id))) {
            $i = 1;

            // header
            $division_structure .= "\r\n\r\n\r\n[table='align:center,width: 500']";
            $division_structure .= "[tr][td]\r\n[center][size=3][b]Leaves of Absence[/b][/size][/center][/td][/tr]";
            $division_structure .= "[/table]\r\n\r\n";

            // players
            $division_structure .= "[table='align:center,width: 500']";
            $loas = LeaveOfAbsence::find_all($this->game_id);

            foreach ($loas as $player) {
                $date_end = (strtotime($player->date_end) < strtotime('now')) ? "[COLOR='#FF0000']Expired " . formatTime(strtotime($player->date_end)) . "[/COLOR]" : date("M d, Y",
                    strtotime($player->date_end));
                $profile = Member::findByMemberId($player->member_id);
                $aod_url = Member::createAODlink(array(
                    'member_id' => $player->member_id,
                    'forum_name' => "AOD_" . $profile->forum_name
                ));

                $division_structure .= "[tr][td]{$aod_url}[/td][td]{$date_end}[/td][td]{$player->reason}[/td][/tr]";
                $i++;
            }

            $division_structure .= "[/table]";

            return $division_structure;
        }

        return $division_structure;
    }
}