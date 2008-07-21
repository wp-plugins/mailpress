<!-- start footer -->
						</td>
						<td style='width:11px; margin:0; padding:0; border:0; background-color:#E7E7E7;'>
							<img src='images/right.jpg' style='margin:0;padding:0;border:0;height:100%;width:10px;' alt=''/>
						</td>
					</tr>
					<tr style='margin:0; padding:0; border:0;width:100%'>
						<td  colspan='3' style='margin:0; padding:0; border:0;height:100%;'>
							<div style='border:0;width:100%;' >
								<table style='width:100%;border:none;table-layout: fixed;'>
									<tr>
										<td style='text-align:center; margin:0; padding:0; border:0;width:1px;'>
											<img src='images/kubrickfooter.jpg' alt=''/>
										</td>
										<td style='text-align:center; margin:0; padding:0; border:0;'>
											<p style="margin:0; padding:10px 0; text-align:center; color:#333; font-family:'Lucida Grande,Verdana,Arial,Sans-Serif'; font-size:.7em;">
												<span>									
													This mail is brought to you by 
													<a style='{color:#06C; text-decoration:none;}' href="http://andrerenaut.ovh.org/wp/?page_id=70" onmouseover="this.style.color='#f00';" onmouseout="this.style.color='#06C';">
														Mailpress
													</a>
												</span>
											</p>
										</td>
									</tr>
								</table>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php if (isset($this->args->unsubscribe)) { ?>
		<small style='color:#6D8C82;'>
			<br/>
			<br/>
			Wish to unsubscribe <a href='{{unsubscribe}}' style='color: rgb(153, 153, 153);font-family: verdana,geneva;font-weight:bold;'>?</a>
		</small>
<?php } ?>
		</div>
	</body>
</html>