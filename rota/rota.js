var crsId;
var fullName;
var committee = false;
var experienced = false;

var committeeMode = false;

var shiftTimes;

var viewModel;

// Get CrsId.
$.getJSON('info.php', function(result) { 

	committee = result.user.committee;

	if (committee && window.location.hash !== '#off') {
		// Set up committee settings.
		viewModel = new CommitteeModel();
		ko.applyBindings(viewModel);
		
		// Show options to change tab.
		$('.committee-options').show();	
	
		// Turn committee mode on or off.
		$('#committee-mode').click(function() {
			committeeMode = $('#committee-mode').hasClass('active') === false;
		});

	} else {
		viewModel = new NonCommitteeModel();
		
		// Bind to just this element.
		// Binding to whole document means some bindings don't exist, so get JS errors.
		ko.applyBindings(viewModel, document.getElementById('mail'));		
	}
	
	// Re-render calendar when clicking home tab (doesn't display otherwise).
	$('#show-home').on('shown', function (e) {
		$('#calendar').fullCalendar('render');
	});

	// Set welcome message.
	crsId = result.crsId;
	fullName = result.user.forename + ' ' + result.user.surname;
	experienced = result.user.experienced;
	$('#welcome').text('Welcome, ' + result.user.forename);
});


$(document).ready(function() {
	
	updateShiftTimes();
	
	// Create calendar.
	$('#calendar').fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
			right: ''
		},
		editable: false,
		events: 'shifts.php',
		
		eventClick: function(calEvent, jsEvent, view) {

			if (committee && (jsEvent.ctrlKey || committeeMode)) {
				// Committee edit shift.
				viewModel.BarShiftModel.showEditShift(calEvent.day, calEvent.start, calEvent.workerNumber, calEvent.crsId, calEvent.experiencedOnly);		
			
			} else if (calEvent.shiftAvailable === true) {
				// Assign an available shift.
				
				if (calEvent.experiencedOnly === true && !experienced) {
					// Experienced shift but we are not experienced.
					alert('You are not an experienced worker.');
					
				} else {
					var shiftTime = getShiftTimes(new Date(calEvent.start).getDay(), calEvent.workerNumber, shiftTimes);
				
					if (confirm('Sign up for:\nWorker ' + calEvent.workerNumber + '\n' + formatDate(calEvent.start) + '\n' + shiftTime)) {
						assignShift(calEvent, fullName);
					}
				}
				
			} else if (calEvent.crsId === crsId) {
				// If user has the shift, make it available.
									
				if (confirm('Release your shift:\nWorker ' + calEvent.workerNumber + '\n' + formatDate(calEvent.start))) {
					assignShift(calEvent, fullName);
				}
			}
		},
		
		eventMouseover: function(calEvent, jsEvent, view) {
			var shiftTime = getShiftTimes(new Date(calEvent.start).getDay(), calEvent.workerNumber, shiftTimes);
			$(jsEvent.toElement).attr('alt', shiftTime);
			$(jsEvent.toElement).attr('title', shiftTime);
		}
	});
		
	// Close error box.
	$('#close-error').click(function() {
		showError(null);
	});
	
	// Close success box.
	$('#close-success').click(function() {
		showSuccess(null);
	});
		
	
	// Update events every minute.
	setInterval(function() {
		$('#calendar').fullCalendar('refetchEvents');	
		updateShiftTimes();	
	}, 60 * 1000);

});

function updateShiftTimes() {
	$.getJSON('default-times.php', function (result) {
		shiftTimes = result.defaultTimes;
	});
}

function getShiftTimes(day, workerNumber, shiftTimes) {
	var shift = shiftTimes[day][workerNumber];
	return shift.start + ' - ' + shift.end;
}

function formatDate(date) {
	// Format date as e.g: Sat 11th Aug 2012.
	return $.fullCalendar.formatDate(date, 'ddd dS MMM yyyy');
}

function showError(error) {
	// Show an error message if there is one, else hide error box.
	// Hide after 60 seconds.
	
	if (error) {
		$('#error').hide();
		$('#error-message').html('<strong>Error!</strong> ' + error);	
		$('#error').show('slow');
		
		setTimeout(function() {
			showError(null);		
		}, 60 * 1000);
		
	} else {
		$('#error').hide('slow');
	}
}

function showSuccess(message) {
	// Show a success message if there is one, else hide success box.
	// Hide after 60 seconds.
	
	if (message) {
		$('#success').hide();
		$('#success-message').html('<strong>Success!</strong> ' + message);	
		$('#success').show('slow');
		
		setTimeout(function() {
			showSuccess(null);		
		}, 60 * 1000);
		
	} else {
		$('#success').hide('slow');
	}
}

function assignShift(calEvent, person) {
	// Assign the shift to the user, or make available if it is their own shift.
	
	$.post('assign-shift.php', { shiftId: calEvent.day, workerNumber: calEvent.workerNumber }, function(result) {
												
		if (!result.error) {
			// There has not been an error.
			
			// Update event with worker crsId.
			calEvent.title = person;
			calEvent.crsId = result.crsId;
			
			calEvent.shiftAvailable = result.shiftAvailable;
			
			if (calEvent.shiftAvailable) {
				// Shift has been made available.
				calEvent.title = '[' + person + ' - Available]';
				calEvent.color = '#36C';
				showSuccess('You have released Worker ' + calEvent.workerNumber + ' on ' + formatDate(calEvent.start));
				
			} else {
				// Shift has been taken.
				calEvent.color = 'green';
				showSuccess('You are signed up for Worker ' + calEvent.workerNumber + ' on ' + formatDate(calEvent.start));
			}
		
			// Re-render event on calendar.
			$('#calendar').fullCalendar('updateEvent', calEvent);
			showError(null);
		
		} else {
			showError(result.error);	
			showSuccess(null);
		}
	
	}, 'json');					
}

/* VIEW MODEL */

function NonCommitteeModel() {
	var self = this;
	
	self.EmailModel = new EmailModel();
}
