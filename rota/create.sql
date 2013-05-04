CREATE TABLE Shifts (
  ShiftId int(11) NOT NULL AUTO_INCREMENT,
  `Date` date NOT NULL,
  PRIMARY KEY (ShiftId),
  UNIQUE KEY `Date` (`Date`)
) ENGINE=InnoDB;

CREATE TABLE Staff (
  CrsId varchar(10) NOT NULL,
  Forename varchar(25) NOT NULL,
  Surname varchar(25) NOT NULL,
  PhoneNumber varchar(11) NOT NULL,
  Experienced tinyint(1) NOT NULL DEFAULT '0',
  Committee tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (CrsId)
) ENGINE=InnoDB;

CREATE TABLE Workers (
  ShiftId int(11) NOT NULL,
  WorkerNumber int(11) NOT NULL,
  CrsId varchar(10),
  Available tinyint(1) NOT NULL DEFAULT '1',
  Experienced tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (ShiftId,WorkerNumber),
  FOREIGN KEY (ShiftId) REFERENCES Shifts(ShiftId) ON DELETE CASCADE,
  FOREIGN KEY (CrsId) REFERENCES Staff(CrsId) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE DefaultShifts (
  Day int(1) NOT NULL,
  Weekday varchar(9) NOT NULL,
  Amount tinyint(4) NOT NULL,
  Steward tinyint(1) NOT NULL DEFAULT '0',
  OnCall tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`Day`)
) ENGINE=InnoDB;

CREATE TABLE BarOpening (
  Type varchar(5) NOT NULL,
  `Date` date NOT NULL,
  PRIMARY KEY (Type)
) ENGINE=InnoDB;

CREATE TABLE DefaultTimes (
  WorkerNumber int(11) NOT NULL,
  Day int(11) NOT NULL,
  Start TIME NOT NULL,
  End TIME NOT NULL,
  PRIMARY KEY (`WorkerNumber`, `Day`)
) ENGINE=InnoDB;


INSERT INTO DefaultShifts (Day, Weekday, Amount, Steward) VALUES (0, 'Sunday', 3, '1', '1');
INSERT INTO DefaultShifts (Day, Weekday, Amount, Steward) VALUES (1, 'Monday', 2, '0', '1');
INSERT INTO DefaultShifts (Day, Weekday, Amount, Steward) VALUES (2, 'Tuesday', 2, '0', '1');
INSERT INTO DefaultShifts (Day, Weekday, Amount, Steward) VALUES (3, 'Wednesday', 3, '1', '1');
INSERT INTO DefaultShifts (Day, Weekday, Amount, Steward) VALUES (4, 'Thursday', 2, '0', '1');
INSERT INTO DefaultShifts (Day, Weekday, Amount, Steward) VALUES (5, 'Friday', 3, '1', '1');
INSERT INTO DefaultShifts (Day, Weekday, Amount, Steward) VALUES (6, 'Saturday', 2, '0', '1');


INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (0, 0, '193000', '233000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (1, 0, '193000', '233000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (2, 0, '193000', '233000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (3, 0, '193000', '233000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (4, 0, '193000', '233000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (5, 0, '193000', '000000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (6, 0, '193000', '000000');

INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (0, 1, '193000', '233000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (1, 1, '193000', '233000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (2, 1, '193000', '233000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (3, 1, '193000', '233000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (4, 1, '193000', '233000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (5, 1, '193000', '000000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (6, 1, '193000', '000000');

INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (0, 2, '200000', '230000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (1, 2, '200000', '230000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (2, 2, '200000', '230000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (3, 2, '200000', '230000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (4, 2, '200000', '230000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (5, 2, '200000', '233000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (6, 2, '200000', '233000');

INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (0, 3, '210000', '233000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (1, 3, '210000', '233000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (2, 3, '210000', '233000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (3, 3, '210000', '233000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (4, 3, '210000', '233000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (5, 3, '210000', '233000');
INSERT INTO DefaultTimes (Day, WorkerNumber, Start, End) VALUES (6, 3, '210000', '233000');


INSERT INTO BarOpening (Type, Date) VALUES('open', CURDATE());
INSERT INTO BarOpening (Type, Date) VALUES('close', CURDATE());
