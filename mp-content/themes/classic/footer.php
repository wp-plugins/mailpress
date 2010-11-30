<!-- start footer -->
								</div>
							</td>
							<td></td>
						</tr>


						<tr <?php $this->classes('ftr'); ?>>
							<td <?php $this->classes('ftd'); ?>>
								<cite <?php $this->classes('fcite'); ?>>
									This mail is brought to you by 
									<a <?php $this->classes('fcitea'); ?> href='http://andrerenaut.ovh.org/wp/?page_id=70' onmouseover="this.style.color='#fff';" onmouseout="this.style.color='#000';">
										<strong <?php $this->classes('nopmb'); ?>>
MailPress
										</strong>
									</a>
								</cite>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td></td>
						</tr>
					</tbody>
				</table>
			</div>
<?php if (isset($this->args->unsubscribe)) { ?>
			<small <?php $this->classes('small'); ?>>
				<br />
				<br />
				Wish to unsubscribe <a href='{{unsubscribe}}' <?php $this->classes('small'); ?>>?</a>
			</small>
<?php } ?>
		</div>
	</body>
</html>