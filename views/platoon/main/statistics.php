<?php $pltCount = Platoon::countPlatoon($platoon->id); ?>
<div class='panel panel-primary'>
	<div class='panel-heading'>Total Members</div>
	<div class='panel-body count-detail-big striped-bg'><span class='count-animated'><?php echo $pltCount; ?></span>
	</div>
</div>
<?php if ($pltCount): ?>
	<div class='panel panel-primary'>
		<div class='panel-heading'>Forum Activity</div>
		<div class='panel-body striped-bg'>
			<div id="canvas-holder" data-stats="<?php echo htmlentities($activity, ENT_QUOTES, 'UTF-8'); ?>">
				<canvas id="chart-area" />
			</div>
		</div>
	</div>
<?php endif; ?>
