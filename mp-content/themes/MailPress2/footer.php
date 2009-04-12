<!-- start footer -->
			</div>
			<div style='clear:both;'></div>
			<table <?php mp_classes('ftable'); ?>>
				<tr>	
					<td <?php mp_classes('fltd'); ?>>
						<b>
							This mail is brought to you by MailPress.
						</b>
					</td>
					<td <?php mp_classes('frtd'); ?>>
						<b>
							MAIL IS SHARING POETRY
						</b>
					</td>
				</tr>
			</table>
		</div>
<?php if (isset($this->args->unsubscribe)) { ?>
		<small style='color:#6D8C82;'>
			<br/>
			<br/>
			Manage your <a href='{{unsubscribe}}' <?php mp_classes('globlink'); ?>>subscriptions</a>
		</small>
<?php } ?>
	</body>
</html>