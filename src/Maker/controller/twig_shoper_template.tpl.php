<?php echo $helper->getHeadPrintCode("Hello $class_name!"); ?>

{% block body %}

 <div class="container">
	<div class="row">
		<div class="col_grow">
			<div class="box box_spacing box_primary mt-2">
                <h1>Hello {{ controller_name }}! ✅</h1>
				This friendly message is coming from:
                <ul class="list">
                    <li>Your controller at <code><?php echo $helper->getFileLink("$root_directory/$controller_path", "$controller_path"); ?></code></li>
                    <li>Your template at <code><?php echo $helper->getFileLink("$root_directory/$relative_path", "$relative_path"); ?></code></li>
                </ul>
			</div>
		</div>
	</div>
</div>
    
</div>
{% endblock %}