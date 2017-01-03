<div class="panel panel-info">
	<div class="panel-heading">
		<div class="panel-title">
			<a href="#" data-toggle="collapse" data-target="#moreinfo" class="collapsed" aria-expanded="false"><i class="glyphicon glyphicon-info-sign"></i></a>&nbsp;&nbsp;&nbsp;<?php echo _('What is Alarm Dialer?')?></div>
	</div>
	<!--At some point we can probably kill this... Maybe make is a 1 time panel that may be dismissed-->
	<div class="panel-body collapse" id="moreinfo" aria-expanded="false" style="height: 30px;">
		<p><?php echo sprintf(_('Alarm Dialer can be used to place calls until an alarm is marked as received. To create an Alarm, dial %s.'),$code)?></p>
	</div>
</div>
<ul class="nav nav-tabs" role="tablist">
	<li data-name="tab1" class="change-tab active"><a href="#tab1" aria-controls="tab1" role="tab" data-toggle="tab" id="list"><?php echo _('Call List')?></a></li>
	<li data-name="tab2" class="change-tab"><a href="#tab2" aria-controls="tab2" role="tab" data-toggle="tab" id="settings"><?php echo _('Settings')?></a></li>
</ul>
<div class="tab-content display">
	<div id="tab1" class="tab-pane active">
		<div id="toolbar-all">
			<button id="remove-all" class="btn btn-danger btn-remove" data-type="extensions" disabled data-section="all">
				<i class="glyphicon glyphicon-remove"></i> <span><?php echo _('Delete')?></span>
			</button>
			<span class="btn btn-time disabled">
				<b><?php echo _("Server time")?>:</b>
				<div id="servertime" data-time="<?php echo time()?>" data-zone="<?php echo date("e")?>" style="display: inline;"><span><?php echo _("Not received")?></span></div>
			</span>
		</div>
		<table id="callgrid" data-url="ajax.php?module=alarmdialer&amp;command=getable" data-cache="false" data-toolbar="#toolbar-all" data-maintain-selected="true" data-show-columns="true" data-show-toggle="true" data-toggle="table" data-pagination="true" data-search="true" class="table table-striped">
			<thead>
				<tr>
					<th data-field="time"><?php echo _("Time")?></th>
					<th data-field="date"><?php echo _("Date")?></th>
					<th data-field="destination"><?php echo _("Destination")?></th>
					<th data-field="actions"><?php echo _("Actions")?></th>
				</tr>
			</thead>
		</table>
	</div>
	<div id="tab2" class="tab-pane">
		<form class="fpbx-submit" action="?display=alarmdialer" method="post">
			<input type="hidden" name="action" value="settings">
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="extensionlength"><?php echo _('Max Destination Length')?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="extensionlength"></i>
								</div>
								<div class="col-md-9">
									<input class="form-control" type="text" name="extensionlength" id="extensionlength" value="<?php echo $config['extensionlength']?>">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="extensionlength-help" class="help-block fpbx-help-block">
							<?php echo _('This controls the maximum number of digits an operator can send a wakeup call to. Set to 10 or 11 to allow wake up calls to outside numbers')?>
						</span>
					</div>
				</div>
			</div>
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="waittime"><?php echo _('Ring Time')?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="waittime"></i>
								</div>
								<div class="col-md-9">
									<input class="form-control" type="text" name="waittime" id="waittime" value="<?php echo $config['waittime']?>">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="waittime-help" class="help-block fpbx-help-block">
							<?php echo _('The number of seconds for the phone to ring. Consider setting lower than the voicemail threshold or the wakeup call can end up going to voicemail')?>
						</span>
					</div>
				</div>
			</div>
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="retrytime"><?php echo _('Retry Time')?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="retrytime"></i>
								</div>
								<div class="col-md-9">
									<input class="form-control" type="text" name="retrytime" id="retrytime" value="<?php echo $config['retrytime']?>">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="retrytime-help" class="help-block fpbx-help-block">
							<?php echo _('The number of seconds to wait between retrys.  A "retry" happens if the wakeup call is not answered')?>
						</span>
					</div>
				</div>
			</div>
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="maxretries"><?php echo _('Max Retries')?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="maxretries"></i>
								</div>
								<div class="col-md-9">
									<input class="form-control" type="text" name="maxretries" id="maxretries" value="<?php echo $config['maxretries']?>">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="maxretries-help" class="help-block fpbx-help-block">
							<?php echo _('The maximum number of times the system should attempt to deliver the wakeup call when there is no answer. Zero retries means only one call will be placed')?>
						</span>
					</div>
				</div>
			</div>
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="callerid"><?php echo _('Wake Up Caller ID')?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="callerid"></i>
								</div>
								<div class="col-md-9">
									<input class="form-control" type="text" name="callerid" id="callerid" value="<?php echo htmlentities($config['callerid'])?>">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="callerid-help" class="help-block fpbx-help-block">
							<?php echo _('CallerID for Wake Up Calls<br><br>Format: <b>&lt;#######&gt;</b>. You can also use the format: "hidden" <b>&lt;#######&gt;</b> to hide the CallerID sent out over Digital lines if supported (E1/T1/J1/BRI/SIP/IAX)')?></span>
					</div>
				</div>
			</div>
			<div class="element-container">
				<div class="row">
					<div class="col-md-12">
						<div class="row">
							<div class="form-group">
								<div class="col-md-3">
									<label class="control-label" for="destination"><?php echo _('Call Destination')?></label>
									<i class="fa fa-question-circle fpbx-help-icon" data-for="destination"></i>
								</div>
								<div class="col-md-9">
									<input class="form-control" type="text" name="destination" id="destination" value="<?php echo $config['destination']?>">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<span id="destination-help" class="help-block fpbx-help-block">
							<?php echo _('Destination for Alarm Calls')?></span>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel"><?php echo _('Add new Wakeup Call')?></h4>
			</div>
			<div class="modal-body">
				<form id="callform">
					<div class="fpbx-container">
						<div class="display no-border">
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="destination"><?php echo _('Destination')?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="destination"></i>
												</div>
												<div class="col-md-9"><input type="text" class="form-control" name="destination" id="destination"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="destination-help" class="help-block fpbx-help-block"><?php echo _('Destination to call')?></span>
									</div>
								</div>
							</div>
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="time"><?php echo _('Time')?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="time"></i>
												</div>
												<div class="col-md-9"><input type="text" class="form-control" id="time"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="time-help" class="help-block fpbx-help-block"><?php echo _('Time to call')?></span>
									</div>
								</div>
							</div>
							<div class="element-container">
								<div class="row">
									<div class="col-md-12">
										<div class="row">
											<div class="form-group">
												<div class="col-md-3">
													<label class="control-label" for="day"><?php echo _('Day')?></label>
													<i class="fa fa-question-circle fpbx-help-icon" data-for="day"></i>
												</div>
												<div class="col-md-9"><input type="text" class="form-control" id="day"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<span id="day-help" class="help-block fpbx-help-block"><?php echo _('Day to call')?></span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _('Close')?></button>
				<button type="button" class="btn btn-primary" id="savecall"><?php echo _('Save changes')?></button>
			</div>
		</div>
	</div>
</div>
