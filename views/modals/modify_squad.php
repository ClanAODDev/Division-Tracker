<?php $leaders = Platoon::SquadLeaders($_POST['division_id']); ?>

<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<h4 class="modal-title"><strong>Modify</strong></h4>
</div>

<form id="modify_squad">

	<div class="modal-body">

		<p>Select a leader to assign. Or select none to create a TBA, to be assigned later. If the player you want is not listed, ensure they have the correct permission and that they aren't already assigned elsewhere.</p>

		<input type='hidden' name='squad_id' value='<?php echo $_POST['squad_id'] ?>'></input>

		<div class="form-group">
			<select name="leader_id" class="form-control">

				<?php if (count((array) $leaders)): ?>
					<?php foreach($leaders as $leader): ?>
						<option value="<?php echo $leader->id ?>"><?php echo Rank::convert($leader->rank_id)->abbr . " " . ucwords($leader->forum_name); ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
				<option value="0">None</option>

			</select>
		</div>

	</div>

	<div class="modal-footer">
		<button type="button" class="btn btn-danger pull-left" id="delete_squad_btn"><i class="fa fa-trash"></i> Delete</button>
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		<button type="button" class="btn btn-success" id="modify_squad_btn">Save</button>
	</div>

</form>
