<div class="wa_learning_path_nav_content">
    <div class="log"></div>
    <div class="tabs-wrapper">
		<div class="learning_plan_name"><?php echo $learningpath->title ?></div>
		<div class="sub_level">
			<div class="<?php if($action == 'view') echo 'selected' ?>"><a href="<?php echo $urlinstraction; ?>"><?php echo get_string('summary_and_instruction', $this->pluginname) ?></a></div>
			<div class="<?php if($action == 'matrix' && empty($key)) echo 'selected' ?> ?>"><a href="<?php echo $urlmatrix; ?>"><?php echo get_string('learning_path_matrix', $this->pluginname) ?></a></div>
            <?php if(!empty($key)): ?>
                <div class="sub_level">
                    <div class="<?php if(!empty($key) && empty($position)) echo 'selected' ?> ?>"><a href="<?php echo $urlcell; ?>"><?php echo $r_label .', '. $c_label ?></a></div>
                    <?php if(!empty($position)): ?>
                        <div class="sub_level">
                            <div class="<?php if(!empty($position)) echo 'selected' ?> ?>"><a href="<?php echo $urlposition; ?>#activities_tab"><?php echo get_string($position, 'local_wa_learning_path'); ?></a></div>
                        </div>
                    <?php endif ?>
                </div>
            <?php endif; ?>
		</div>
		<div class="clearfix"></div>
        <?php if(!$previewmode): ?>
            <br />
            <form action="<?php echo $subscribeurl ?>" method="post">
                <input type="hidden" id="<?php echo (int) $learningpath->id ?>" />
                <button
                    onclick="$(this).parent().submit(); $(this).attr('disabled', true);"
                    class="btn <?php echo ($learningpath->subscribed) ? 'btn-default' : 'btn-primary' ?> pull-right"
                    data-toggle="tooltip" data-placement="bottom"
                    <?php if ($learningpath->subscribed) : ?>
                        title="<?php echo get_string('unsubscribe_info', 'local_wa_learning_path'); ?>"
                    <?php else : ?>
                        title="<?php echo get_string('subscribe_info', 'local_wa_learning_path'); ?>"
                    <?php endif; ?>
                    >
                    <?php echo ($learningpath->subscribed) ? get_string('unsubscribe', $this->pluginname) : get_string('subscribe', $this->pluginname) ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
	<div class="clearfix"></div>
   <?php if($action == 'matrix'): ?>
        <br />
        <div><?php echo get_string('show_path_for', $this->pluginname) ?>:</div>
        <select name="levels" class="levels" data-placeholder="<?php echo get_string('all_levels', $this->pluginname) ?>" multiple  >
            <?php foreach($matrixlevels as $level): ?>
                <?php if(!$level->show) continue; ?>
            <option value="<?php echo $level->id ?>" <?php if (in_array($level->id, $l_selected)) echo 'selected="selected"'; ?>><?php echo $level->name ?></option>
            <?php endforeach; ?>
        </select>
        <br /><br />
        <div><?php echo get_string('region_filter', $this->pluginname) ?>:</div>
        <select name="region" class="region" data-placeholder="<?php echo get_string('all_regions', $this->pluginname) ?>" multiple>
            <?php foreach($allregions as $id => $region): ?>
                <option value="<?php echo $id ?>" <?php if( in_array($id, $r_selected )) echo 'selected="selected"'; ?>><?php echo $region ?></option>
            <?php endforeach; ?>
        </select>
        <br /><br />
        <a class="btn btn-default pull-left update_filters" href="<?php echo $urlmatrix ?>"><?php echo get_string('update_filters', $this->pluginname) ?></a>
        <a class="btn btn-default pull-right clear_filters" href="<?php echo $urlclear ?>"><?php echo get_string('clear_filters', $this->pluginname) ?></a>
    <?php endif; ?>
</div>
<div class="clearfix"></div>
<script type='text/javascript'>
	$(document).ready(function () {
        $('.wa_learning_path_nav_content .levels').chosen({width: "100%"});
        $('.wa_learning_path_nav_content .region').chosen({width: "100%"});

        $('.wa_learning_path_nav_content .update_filters').click(function(){
            var levels = '';
            var regions = '';

            if($('.wa_learning_path_nav_content .levels').val()) {
                levels = $('.wa_learning_path_nav_content .levels').val().join();
            }

            if($('.wa_learning_path_nav_content .region').val()) {
                regions = $('.wa_learning_path_nav_content .region').val().join();
            }

            var url = $(this).prop('href') + '&levels=' + levels + '&regions=' + regions;
            $(this).prop('href', url);
        });


	});
</script>
