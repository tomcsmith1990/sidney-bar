/* DEFAULT NUMBER OF SHIFTS FOR EACH DAY */

function DefaultShiftModel() {
	var self = this;

	self.barOpen = ko.observable();
	self.barClose = ko.observable();
	
	self.defaultShifts = ko.observableArray();
	self.defaultTimes = ko.observableArray();
	
	self.getBarOperatingDates = function () {
		$.getJSON('open-dates.php', function (result) { 
			self.barOpen(result.dates.open);
			self.barClose(result.dates.close);
		});
	};
	
	self.updateDefaultShifts = function () {
		$.getJSON('default-shifts.php', function (result) { 
			var defaults = [];
			for(var day in result.defaultWorkers) {
				var weekday = result.defaultWorkers[day];
				defaults.push({ 'day' : weekday.day, 'workers' : weekday.workers, 'steward' : weekday.steward, 'oncall' : weekday.oncall });
			}
			self.defaultShifts(defaults);
		});
	};
	
	self.setBarOperatingDates = function () {
		// Set dates which bar is open or closed.		
		$.post('edit-open-dates.php', { 'open' : self.barOpen(), 'close' : self.barClose() }, function (result) {
			validateResult(result, 'Bar operating dates have been updated.', function () {
				
				// Update events.
				$('#calendar').fullCalendar('refetchEvents');	
				
			});
		}, 'json');
	};
	
	self.setDefaultShifts = function () {
		// Set default number of workers for each day.		
		$.post('edit-default-shifts.php', { 'defaults' : ko.toJSON(self.defaultShifts()) }, function (result) {
			validateResult(result, 'Default number of shifts have been updated.', function () {
			
				// Update events.
				$('#calendar').fullCalendar('refetchEvents');
				
			});
		}, 'json');
	};

	self.updateDefaultTimes = function () {
		$.getJSON('default-times.php', function (result) { 
			var defaults = [];
			var weekday = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
			for(var day in result.defaultTimes) {
				for (var worker in result.defaultTimes[day]) {
				
					var times = result.defaultTimes[day][worker];

					var workerName = 'Worker ' + worker;
					if (worker === '0') workerName = 'On-Call';
					if (worker === '1') workerName = 'Experienced';

					defaults.push({ 'day' : day, 'weekday' : weekday[day], 'worker' : worker, 'workerName' : workerName,  'start' : times.start, 'end' : times.end });
				}
			}
			self.defaultTimes(defaults);
		});
	};

	self.setDefaultTimes = function () {
		// Set default shift times.	
		$.post('edit-default-times.php', { 'defaults' : ko.toJSON(self.defaultTimes()) }, function (result) {
			validateResult(result, 'Default shift times have been updated.', function () {
				// Update in calendar alt texts.
				updateShiftTimes();
			});
		}, 'json');
	};

	
	self.getBarOperatingDates();
	self.updateDefaultShifts();
	self.updateDefaultTimes();
};

/* ----------------------------------------- */


/* SHIFTS */

function ShiftModel(date) {
	var self = this;
	
	self.shiftId = ko.observable();
	self.date = ko.observable(date);
	self.prettyDate;
	self.workerNumber = ko.observable();
	self.worker = ko.observable();
	self.experiencedOnly = ko.observable(false);
}

function BarShiftModel() {
	var self = this;

	self.shiftToAdd = ko.observable(new ShiftModel($.datepicker.formatDate('yy-mm-dd', new Date())));
	self.shiftToEdit = ko.observable(null);
	
	self.addShift = function () {
		// Create a new shift on date.
		
		$.post('create-shift.php', ko.toJS(self.shiftToAdd()), function (result) {
			validateResult(result, 'Created Worker ' + result.worker + ' shift on ' + formatDate(new Date(result.date)), function () {
				// Update events
				$('#calendar').fullCalendar('refetchEvents');
			});
		}, 'json');	
	};
	
	self.showEditShift = function (shiftId, date, workerNumber, worker, experiencedOnly) {
		var barShift = new ShiftModel();
			barShift.shiftId(shiftId);
			barShift.date($.datepicker.formatDate('yy-mm-dd', new Date(date)));
			barShift.prettyDate = formatDate(new Date(date));
			barShift.workerNumber(workerNumber);
			barShift.worker(worker);
			barShift.experiencedOnly(experiencedOnly);

		self.shiftToEdit(barShift);
		showTab('show-settings');
	};
	
	self.cancelEditShift = function () {
		self.shiftToEdit(null);
		showTab();
	};
	
	self.editShift = function () {
		// Assign the shift to the user, or make available if it is their own shift.
		
		$.post('edit-shift.php', ko.toJS(self.shiftToEdit()), function (result) {
			var success = '';		
			if (!result.error) {
				if (result.shiftAvailable) {
					// Shift has been made available.		
					success = 'Worker ' + result.workerNumber + ' on '+ formatDate(new Date(result.date)) + ' is now available';
				
				} else {
					// Shift has been taken.			
					success = result.crsId + ' is now Worker ' + result.workerNumber + ' on ' + formatDate(new Date(result.date));
				}
			}
			
			validateResult(result, success, function () {
				self.shiftToEdit(null);
				$('#calendar').fullCalendar('refetchEvents');
				showTab();
			});
	
		}, 'json');	
	};
	
	self.deleteShift = function () {
		// Delete the shift for the day and worker.
	
		$.post('delete-shift.php', ko.toJS(self.shiftToEdit()), function (result) {
			validateResult(result, 'Deleted Worker ' + result.worker + ' shift on ' + formatDate(new Date(result.date)), function() {
		
				$('#calendar').fullCalendar('refetchEvents');
				self.shiftToEdit(null);
				showTab();
			});
		}, 'json');
	};
}

function HistoryModel() {
	var self = this;
	
	var d = new Date();
	d.setDate(d.getDate() - 1);
	self.date = ko.observable($.datepicker.formatDate('yy-mm-dd', d));
	
	self.lookupWorkers = function () {
		$.post('lookup-workers.php', { 'date' : self.date() }, function (result) {
			var history = '';
			if (result.length > 0) {
				var crsId;
				var worker;
				for (var i = 0; i < result.length; i++) {
					worker = result[i].worker;
					crsId = result[i].crsId;
					switch (worker) {
						case '0': history += 'On-Call Worker: '; break;
						case '1': history += 'Experienced Worker: '; break;
						default: history += 'Worker ' + worker + ': '; break;
					}
					history += result[i].forename + ' ' + result[i].surname + ', ' + crsId + '\n';
				}
			} else {
				history = 'Could not get workers for ' + self.date();
			}
			alert(history);
		}, 'json');
	};
}

/* ----------------------------------------- */

/* STAFF */

function StaffMemberModel() {
	var self = this;
	
	self.crsId = ko.observable('');
	self.forename = ko.observable('');
	self.surname = ko.observable('');
	self.phone = ko.observable('');
	self.experienced = ko.observable(false);
	self.committee = ko.observable(false);
	
}

function StaffListModel() {

	var self = this;
	
	self.staffMemberToAdd = ko.observable(new StaffMemberModel());
	self.staffMemberToEdit = ko.observable(null);
	
	self.staffList = ko.observableArray();

	self.updateStaffList = function () {
		// Get staff list from server.
		$.getJSON('staff.php', function (result) { 
			self.staffList(result.staff);						
		});
	};
	
	self.addStaffMember = function () {
	
		$.post('add-staff.php', ko.toJS(self.staffMemberToAdd()), function (result) {
			validateResult(result, 'Added ' + result.forename + ' ' + result.surname + ' to staff list.', function () {
			
				// Add new staff member to the observable array.
				self.staffList.push(ko.toJS(self.staffMemberToAdd));
				// Clear form by resetting new staff member.
				self.staffMemberToAdd(new StaffMemberModel());
				
			});
		}, 'json');
	};
	
	self.showEditStaffMember = function (staffMember) {
		// Show the edit staff form with this staff member.
		self.staffMemberToEdit(staffMember);
	};
	
	self.cancelEditStaffMember = function () {
		// Hide the edit staff form.
		self.staffMemberToEdit(null);
	};
	
	self.editStaffMember = function () {

		$.post('edit-staff.php', ko.toJS(self.staffMemberToEdit()), function (result) {
			validateResult(result, 'Updated ' + result.forename + ' ' + result.surname, function () {
			
				// Remove old copy of staff member from array.
				self.staffList.remove(function (item) { return item.crsId === self.staffMemberToEdit().crsId; });
				// Add edited copy to array.
				self.staffList.push(ko.toJS(self.staffMemberToEdit));
				// Reset form.
				self.cancelEditStaffMember();
				
			});
		}, 'json');
	};
	
	self.deleteStaffMember = function (staffMember) {

		if (confirm('Are you sure you want to delete ' + staffMember.forename + ' ' + staffMember.surname + '?')) {
			
			$.post('delete-staff.php', staffMember, function (result) {
				validateResult(result, 'Deleted ' + result.forename + ' ' + result.surname + ' from staff list.', function () {
				
					// Delete the staff member from the array.
					self.staffList.remove(staffMember);
					
				});			
			}, 'json');
		}
	};
	
	self.showStaffMemberInfo = function (staffMember) {
		$.post('staff-info.php', staffMember, function (result) {

			var lastShift = result.lastShift != undefined ? new Date(result.lastShift).toDateString() : "n/a";
			var shiftsInTwoWeeks = result.shiftsInTwoWeeks;
			var shiftsInOneMonth = result.shiftsInOneMonth;
			var shiftsInTwoMonths = result.shiftsInTwoMonths;
			
			alert('Last shift: ' + lastShift + '\n' + 
					'Shifts in past 2 weeks:\t' + shiftsInTwoWeeks + '\n' +
					'Shifts in past month:\t\t' + shiftsInOneMonth + '\n' +
					'Shifts in past 2 months:\t' + shiftsInTwoMonths);
			
		}, 'json');
	};
	
	// Update on initialisation, and update every 60 seconds.
	self.updateStaffList();
	setInterval(self.updateStaffList, 60 * 1000);
}


/* ----------------------------------------- */

/* EMAIL */


function EmailModel() {
	var self = this;
	
	self.experienced = ko.observable(false);
	self.committee = ko.observable(false);
	
	self.subject = ko.observable('');
	self.body = ko.observable('');
	
	self.send = function () {
		
		$.post('email-staff.php', 
			{ fromName: fullName, subject: self.subject(), body: self.body(), experienced: self.experienced(), committee: self.committee() }, 
			function (result) {
				validateResult(result, 'Sent message \'' + result.subject + '\'', function () {			
					// Reset form.
					self.reset();			
				});
		}, 'json');
	};
	
	self.reset = function () {
		self.subject('');
		self.body('');
		self.experienced(false);
		self.committee(false);
	};
	
}

/* ----------------------------------------- */

function CommitteeModel() {
	var self = this;
	
	self.BarShiftModel = new BarShiftModel();
	self.StaffListModel = new StaffListModel();
	self.EmailModel = new EmailModel();
	self.DefaultShiftModel = new DefaultShiftModel();
	self.HistoryModel = new HistoryModel();
}

function showTab(tabId) {
	if (tabId === undefined) tabId = 'show-home';
	
	$('#' + tabId).tab('show');
}

function validateResult(result, successMessage, callback) {

	if (!result.error) {
		// There has not been an error.

		if (typeof(callback) === 'function') callback();
		
		showSuccess(successMessage);
		showError(null);

	} else {
		showError(result.error);	
		showSuccess(null);
	}
}
