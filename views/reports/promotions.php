<?php

foreach ($promotions->members as $member) {
    $bbcodeData[] = $member['forum_name'];
}

?>

<div class="container">

    <ul class='breadcrumb'>
        <li><a href='./'>Home</a></li>
        <li><a href="./divisions/<?php echo $division->short_name; ?>"><?php echo $division->full_name ?></a></li>
        <li class='active'>Promotions</li>
    </ul>

    <div class='page-header'>

        <h2>
            <strong><img
                    src='assets/images/game_icons/48x48/<?php echo $division->short_name; ?>.png'/>
                Promotions
            </strong>
            <small>
                <?php echo $division->full_name; ?>
                Division
            </small>
        </h2>
    </div>


    <div class="row">
        <div class="col-md-7">
            <div class="panel panel-primary">
                <div class="panel-heading">Recently promoted (<?php echo count($promotions->members); ?>)</div>

                <div class="panel-body"><p>
                        <strong>Note: </strong>Recruits automatically reflect their last promoted date as the date they were inducted into the clan, and are not included in this report.
                    </p></div>

                <?php if (count($promotions->members)): ?>
                    <div class="list-group">
                        <?php foreach ($promotions->members as $member): ?>
                            <a class="list-group-item" href="./member/<?php echo $member['member_id'] ?>">
                                <div class="col-xs-6">
                                    <?php echo Rank::convert($member['rank_id'])->abbr . " " . $member['forum_name'] ?>
                                </div>
                                <div class="col-xs-6 text-right">
                                    <?php echo date('Y-m-d',
                                        strtotime($member['last_promotion'])); ?>
                                </div>
                                <div class="clearfix"></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="list-group-item">No members have been promoted in the past 30 days.</div>
                <?php endif; ?>

            </div>
        </div>
        <div class="col-md-5">
            <div class="panel panel-info">
                <div class="panel-heading">Share information</div>

                    <pre class='well code' id='activity'
                        onClick="$(this).selectText()"><?php echo implode(', ', $bbcodeData); ?></pre>

                <div class="panel-footer text-muted">Bb-code tabular data coming soon</div>
            </div>

        </div>


        <div class="col-md-5">
            <div class='panel panel-primary'>
                <div class='panel-heading'>Promotions by rank</div>
                <div class='panel-body'>

                    <?php
                    $data = array();
                    $colors = array('#505160', '#68829e', '#aebd38', '#598234', '#7d4427', '#2c7873' );

                    foreach ($promotions->stats as $stat) {
                        $random = array_rand($colors);
                        $color = $colors[$random];
                        $highlight = hex2rgb($color);

                        unset($colors[$random]);
                        $data[] = array(
                            'label' => Rank::convert($stat['rank_id'])->desc,
                            'value' => $stat['count'],
                            'color' => $color,
                            'highlight' => "rgba({$highlight[0]}, {$highlight[1]}, {$highlight[2]}, .9)"
                        );

                    }
                    $data = json_encode($data);
                    ?>

                    <div id="canvasPromotions" data-stats="<?php echo htmlentities($data, ENT_QUOTES, 'UTF-8'); ?>">
                        <canvas id="chart" style="width:100%; height: 200px;"/>
                    </div>
                </div>
                <div class="panel-footer text-muted">
                    <small>Reflects activity over the past 30 days</small>
                </div>
            </div>
        </div>

    </div>
</div>