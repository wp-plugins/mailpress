
<?php if (isset($this->args->viewhtml)) { ?>
Html version of this mail -> {{viewhtml}}
<?php } ?>

				this mail is brought to you by MailPress

<?php if (isset($this->args->unsubscribe)) { ?>
Unsubscribe ? -> {{unsubscribe}}
<?php } ?>
