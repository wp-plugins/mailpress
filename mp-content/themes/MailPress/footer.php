<!-- start footer -->
			</div>
			<div style='clear:both;'></div>
			<table <?php $this->classes('ftable'); ?>>
				<tr>	
					<td <?php $this->classes('fltd'); ?>>
						<b>
							This mail is brought to you by MailPress.
						</b>
					</td>
					<td <?php $this->classes('frtd'); ?>>
						<b>
							MAIL IS SHARING POETRY
						</b>
					</td>
				</tr>
			</table>
		</div>
<?php if (isset($this->args->unsubscribe)) { ?>
		<small style='color:#6D8C82;'>
			<br />
			<br />
			Manage your <a href='{{unsubscribe}}' <?php $this->classes('globlink'); ?>>subscriptions</a>
		</small>
<?php } ?>
	</body>
</html>