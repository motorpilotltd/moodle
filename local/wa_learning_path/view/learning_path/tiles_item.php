<div class="tile">
    <div class="image_box" onclick="window.location='<?php echo $lpurl->out(false); ?>'" ><?php echo \wa_learning_path\model\learningpath::get_image_url($item->id, true) ?></div>
    <div class="padd_5">
        <div class="title"><a href="<?php echo $lpurl->out(false); ?>"><?php echo $item->title ?></a></div>
        <div class="short_description"><?php echo $item->summary ?></div>
        <div class="region" title="<?php echo $item->regions_names ? implode(' ', $item->regions_names) : $this->get_string('global') ?>" ><?php echo $item->regions_names ? implode(' ', $item->regions_names) : $this->get_string('global') ?></div>
        <?php if($item->subscribed): ?>
            <div class="subscribed"><?php echo $this->get_string('subscribed') ?></div>    
        <?php endif; ?>
    </div>
</div>