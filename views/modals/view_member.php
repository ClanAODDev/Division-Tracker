<?php $allowEdit = User::canUpdate($user->role); ?>

<div class='modal-header'>
    <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
        <span aria-hidden='true'><i class='fa fa-times-circle'></i></span></button>
    <h4 class='modal-title'>Editing
        <strong><?php echo Rank::convert($member->rank_id)->abbr . " " . $member->forum_name ?></strong></h4>
</div>

<input type='hidden' id='cur_platoon_id' name='cur_platoon_id' value='<?php echo $member->platoon_id ?>'/>
<input type='hidden' id='cur_squad_id' name='cur_squad_id' value='<?php echo $member->squad_id ?>'/>
<input type='hidden' id='cur_position_id' name='cur_position_id' value='<?php echo $member->position_id ?>'/>

<div class='modal-body' style='overflow-y: scroll; max-height: 400px; min-height:350px;'>
    <div class='message alert' style='display: none;'></div>

    <div class="tabbable">

        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#profile" data-toggle="tab"><i class="fa fa-user text-muted fa-lg"></i><span class="hidden-sm hidden-xs">Member Profile</a></span>
            </li>
            <li><a href="#gameinfo" data-toggle="tab"><i class="fa fa-gamepad text-muted fa-lg"></i>
                    <span class="hidden-sm hidden-xs">Game Info</a></span></li>
            <?php if ($user->role > 1): ?>
                <li><a href="#divinfo" data-toggle="tab"><i class="fa fa-cog text-muted fa-lg"></i>
                        <span class="hidden-sm hidden-xs">Division Info</a></span></li>
            <?php endif; ?>
            <li><a href="#aliasinfo" data-toggle="tab"><i class="fa fa-users text-muted fa-lg"></i>
                    <span class="hidden-sm hidden-xs">Aliases</a></span></li>

            <?php if (User::isUser($member->id) && $user->role > 1 || ( ! is_null($userInfo) && $userInfo->id == $user->id)): ?>
                <li class="pull-right text-info">
                    <a href="#userinfo" data-toggle="tab"><i class="fa fa-key text-muted fa-lg"></i>
                        <span class="hidden-sm hidden-xs">User Account</a></li>
            <?php endif; ?>
        </ul>

        <div class="tab-content">

            <div class="tab-pane active" id="profile">
                <form id='member-form'>
                    <input type='hidden' name='id' value='<?php echo $member->id ?>'/>
                    <div class="margin-top-20"></div>
                    <div class="form-group">
                        <label for="forum_name" class="control-label">Forum Name</label>
                        <input type="text" class="form-control" name="forum_name" value="<?php echo $member->forum_name ?>" disabled>
                    </div>

                    <input type="hidden" name="member_id" value="<?php echo $member->member_id ?>">

                    <div class="form-group">
                        <label for="recruiter" class="control-label">Recruiter ID</label>
                        <input type="number" class="form-control" name="recruiter" value="<?php echo $member->recruiter ?>">
                    </div>
                    <div class="margin-top-20"></div>
                </form>
            </div>

            <?php if ($user->role > 1): ?>
                <div class="tab-pane" id="divinfo">
                    <div class="margin-top-20"></div>
                    <form id='div-form'>
                        <div class='form-group position-group' style='display: <?php echo $allowEdit->posField ?>'>
                            <label for='position_id' class='control-label'>Position</label>
                            <select name='position_id' id='position_id' class='form-control'>
                                <?php foreach ($positionsArray as $position) : ?>
                                    <option value='<?php echo $position->id ?>'
                                        <?= ($position->id == $member->position_id) ? "selected" : null; ?>>
                                        <?php echo Locality::run($position->desc, $memberInfo->game_id); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class='form-group platoon-group' style='display: <?php echo $allowEdit->pltField ?>'>
                            <label for='platoon_id' class='control-label'><?php echo Locality::run('Platoon',
                                    $member->game_id); ?></label>
                            <select name='platoon_id' id='platoon_id' class='form-control'>
                                <option value='0'>None (General Sergeant or Division Leader)</option>
                                <?php if (count(Platoon::countPlatoons())) : ?>
                                    <?php foreach ($platoons as $platoon) : ?>
                                        <option value='<?php echo $platoon->id ?>'
                                            <?= ($platoon->id == $member->platoon_id) ? "selected" : null; ?>>
                                            <?php echo $platoon->name ?></option>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <option>No <?php echo Locality::run('platoons', $member->game_id); ?> exist.
                                    </option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class='form-group sqdldr-group' style='display: <?php echo $allowEdit->sqdField ?>'>
                            <label for='squad_id' class='control-label'><?php echo Locality::run('squad',
                                    $member->game_id); ?></label>
                            <select name='squad_id' id='squad_id' class='form-control'>
                                <option value='0' selected>None (Division Leader)</option>

                                <?php if (count(Division::countSquadLeaders($member->game_id))) : ?>

                                    <?php foreach ($squads as $squad) : ?>

                                        <?php $leader = Member::findById($squad->leader_id); ?>
                                        <?php $platoon = Platoon::findById($squad->platoon_id); ?>

                                        <option value='<?php echo $squad->id ?>' <?= ($squad->id == $member->squad_id) ? "selected" : null; ?>>
                                            <?php echo ($squad->leader_id != 0)
                                                ? Rank::convert($leader->rank_id)->abbr . " " . ucwords($leader->forum_name)
                                                : "TBA (Squad #{$squad->id})"; ?> - <?php echo $platoon->name ?></option>

                                    <?php endforeach; ?>

                                <?php endif; ?>
                            </select>
                        </div>

                    </form>
                    <div class="margin-top-20"></div>

                </div>
            <?php endif; ?>

            <div class="tab-pane" id="aliasinfo">
                <form id='alias-form'>
                    <div class="margin-top-20"></div>
                    <div class='form-group handles-group'>
                        <table class="table table-striped table-hover" id="aliases" style="overflow-y: scroll; max-height: 400px;">
                            <?php $memberHandles = MemberHandle::findByMemberId($member->id); ?>
                            <?php if (count($memberHandles)): ?>
                                <?php foreach ($memberHandles as $handle): ?>
                                    <?php if ($handle->isVisible || User::isDev()): ?>
                                        <tr class="member-handle" data-handle-type="<?php echo $handle->handle_type ?>" data-handle-id="<?php echo $handle->id ?>">
                                            <td><strong><?php echo $handle->name; ?></strong></td>
                                            <td>
                                                <input type='text' class='form-control' name='<?php echo $handle->handle_name; ?>' value='<?php echo $handle->handle_value ?>' required/>
                                            </td>
                                            <td></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php $handles = Handle::find_all(); ?>
                            <?php if (count($handles) > (count($memberHandles))): ?>
                                <tr>
                                    <td class="text-muted">Add new alias</td>
                                    <td><select id="alias-selector" class="form-control">
                                            <option value="" disabled selected="selected">Select an alias type</option>
                                            <?php foreach ($handles as $handle): ?>
                                                <option value="<?php echo $handle->id ?>" data-type="<?php echo $handle->type ?>" data-description="<?php echo $handle->name ?>"><?php echo $handle->name ?></option>
                                            <?php endforeach; ?>
                                        </select></td>
                                    <td>
                                        <button class="btn btn-success btn-block add-alias">
                                            <i class="fa fa-plus fa-lg"></i></button>
                                    </td>
                                </tr>
                            <?php endif; ?>

                        </table>
                    </div>
                </form>
                <div class="margin-top-20"></div>
            </div>

            <div class="tab-pane" id="gameinfo">
                <div class="margin-top-20"></div>

                <?php if (count(SubGame::count($member->game_id))): ?>
                    <div class="form-group game-group">
                        <label for='platoon' class='control-label'>Games Played</label><br/>
                        <select id="games" multiple="multiple">
                            <?php foreach (SubGame::get($member->game_id) as $game): ?>
                                <?php $selected = (MemberGame::plays($member->id,
                                    $game->id)) ? "selected='selected'" : ""; ?>
                                <option value="<?php echo $game->id ?>" <?php echo $selected ?>><?php echo $game->full_name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <p class="text-muted">This division has no sub-games to select.</p>
                <?php endif; ?>

                <div class="margin-top-50"></div>
            </div>

            <?php if (User::isUser($member->id) && $user->role > 1 || ( ! is_null($userInfo) && $userInfo->id == $user->id)) : ?>
                <div class="tab-pane" id="userinfo">
                    <div class="margin-top-20"></div>
                    <form id='user-form'>
                        <input type='hidden' name='id' value="<?php echo $userInfo->id ?>"/>
                        <div class='form-group user-group'>
                            <label for='username' class='control-label'>Account Name</label>
                            <input type='text' class='form-control user-form-control' value='<?php echo $userInfo->username ?>' disabled>
                        </div>

                        <div class='form-group email-group'>
                            <label for='email' class='control-label'>Email</label>
                            <input type='email' class='form-control user-form-control' name="email" value='<?php echo $userInfo->email ?>'>
                        </div>

                        <?php if ($user->role > 1 || User::isDev()): ?>
                            <div class='form-group role-group'>
                                <label for='role' class='control-label'>Account Access</label>
                                <select id="role" name="role" class="form-control user-form-control">
                                    <?php foreach ($rolesArray as $role) : ?>
                                        <?php if ($role->id <= $user->role || User::isDev()): ?>
                                            <option value="<?php echo $role->id; ?>" <?php echo ($userInfo->role == $role->id) ? "selected" : null ?> <?php echo ($user->role == $role->id && ! User::isDev()) ? "disabled" : null ?>><?php echo Locality::run($role->role_name,
                                                    $member->game_id); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (User::isDev()): ?>
                            <div class="form-group dev-group">
                                <label class="checkbox-inline"><input class="user-form-control" type="checkbox" name="developer" value="1" <?php echo ($userInfo->developer > 0) ? "checked" : null; ?>><i class="fa fa-user-secret text-danger"></i> Developer Mode</label>
                            </div>
                        <?php endif; ?>
                    </form>
                    <div class="margin-top-20"></div>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>


</div>
<div class='modal-footer'>
    <button type='button' class='btn btn-default' data-dismiss='modal' aria-label='Close'>
        <span aria-hidden='true'>Cancel</span></button>
    <button type='button' class='btn btn-success save-btn' id="submit-form"><i class="fa fa-save fa-lg"></i> Save Info
    </button>
    </form>
</div>

<script src='assets/js/view.js'></script>
