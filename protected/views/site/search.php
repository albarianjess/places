<h1>Search for Places</h1>

<div class="search-query">
	You searched for &quot;<span class="text-blue" id="query"><?=$_REQUEST["q"];?></span>&quot;. Found <span class="text-orange" id="count"><input type="hidden" name="count-field" id="count-field" value="<?php echo count($places); ?>" /><?php echo count($places);?></span> results.
</div>

<ul class="rig columns-4">
    <?php foreach($places as $place):
              $image = $place->load_first_image();
              if(!$image->loaded)
                $image = new PictureObj(1);
                $thumb = $image->get_thumb();
    ?>
    <li>
        <a href="<?php echo Yii::app()->createUrl('place'); ?>?id=<?php echo $place->placename; ?><?php if(isset($place->yearterm)) { ?>&yt=<?php echo $place->yearterm; } ?>&ref=<?php echo $_REQUEST['q']; ?>">
            <div class="image-container">
                <img src="<?php echo $thumb; ?>" width="100%" height="100%" alt="" />
            </div>
            <div class="title"><?php echo $place->placename; ?></div>
            <?php if(isset($place->description) and !empty($place->description)): ?>
            <p><?php echo $place->description; ?></p>
            <?php endif; ?>
            <div class="placetype-<?php echo $place->placetype->machinecode; ?>"><?php echo $place->placetype->singular; ?></div>
         </a>
    </li>
    <?php endforeach; ?>
</ul>

<script>
jQuery(document).ready(function() {
   $("ul.rig li").hover(
       function(){
           $(this).stop().fadeTo("fast",1);
       },
       function(){
           $(this).stop().fadeTo("fast",0.8);
       }
   );
});
</script>