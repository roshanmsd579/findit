USE findit_db;

INSERT INTO users
(student_id, name, email, password, phone, role, department) VALUES
('STU001','Aryan Sharma','aryan@university.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9876543210','student','Computer Science'),
('STU002','Priya Mehta','priya@university.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9123456780','student','Electronics'),
('STU003','Rahul Verma','rahul@university.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9988776655','student','Mechanical'),
('FAC001','Dr. Sunita Patel','sunita@university.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9871234560','faculty','Computer Science'),
('STU004','Vikram Singh','vikram@university.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9765432100','student','Civil'),
('STU005','Anjali Gupta','anjali@university.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9654321009','student','MBA'),
('STF001','Deepak Yadav','deepak@university.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9543210098','staff','Administration'),
('SEC001','Ravi Kumar','ravi@university.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9432100987','security','Security Dept'),
('STU006','Meera Iyer','meera@university.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9321009876','student','BCA'),
('ADM001','Admin User','admin@university.edu','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','9000000001','admin','IT Department');

INSERT INTO reports
(user_id,type,category,title,description,campus_location,latitude,longitude,date_occurred,time_occurred,contact_phone,secret_question,secret_answer,status) VALUES
(1,'lost','id_card','Lost: University ID Card - Aryan Sharma','Lost my university ID card near the library entrance. It has my photo and student ID STU001.','Central Library',28.6139,77.2090,'2025-03-28','09:30:00','9876543210','What is printed on the back of the card?','Emergency: 9000000000','active'),
(2,'found','phone','Found: Black OnePlus Phone near Canteen','Found a black OnePlus smartphone on table 5 in the main canteen. Screen has a small crack.','Main Canteen',28.6142,77.2094,'2025-03-29','13:15:00','9123456780','What is the phone brand?','OnePlus','active'),
(3,'lost','laptop','Lost: Dell Laptop Bag - Lab Block','Left my black Dell laptop bag in CS Lab 3. Has a red sticker on lid. Contains charger and notes.','Lab Block - CS Lab 3',28.6135,77.2085,'2025-03-30','16:00:00','9988776655','What sticker is on the lid?','Red sticker','active'),
(4,'found','wallet','Found: Brown Leather Wallet at Sports Ground','Found a brown leather wallet near the cricket nets. Contains some cash and cards.','Sports Ground',28.6150,77.2100,'2025-03-31','18:30:00','9871234560','What color is the wallet?','Brown','active'),
(5,'lost','keys','Lost: Hostel Room Keys - Block B','Lost my hostel room keys (Room B-204) somewhere between the hostel and admin block.','Hostel Block B',28.6130,77.2080,'2025-04-01','08:00:00','9765432100','What room number is on the keychain?','B-204','active'),
(6,'found','id_card','Found: Library Card near Parking Lot','Found a university library card in the parking lot near Gate 2. Name visible on card.','Parking Lot - Gate 2',28.6145,77.2088,'2025-04-01','11:00:00','9654321009','What is the card type?','Library Card','resolved'),
(7,'lost','phone','Lost: iPhone 13 Blue - Admin Block','Lost my blue iPhone 13 somewhere near the admin block waiting area. Has a clear case.','Admin Block',28.6138,77.2092,'2025-04-02','14:30:00','9543210098','What color is the phone?','Blue','active'),
(8,'found','stationery','Found: Geometry Box and Notes - Lecture Hall 4','Found a blue geometry box and engineering drawing notes in Lecture Hall 4, Row C.','Lecture Hall 4',28.6136,77.2087,'2025-04-02','10:00:00','9432100987','What was found along with geometry box?','Notes','active'),
(9,'lost','clothing','Lost: Black Hoodie - Seminar Hall','Left my black hoodie with university logo in the seminar hall after the technical fest.','Seminar Hall',28.6140,77.2091,'2025-04-03','17:00:00','9321009876','What logo is on the hoodie?','University logo','active'),
(10,'found','keys','Found: Bike Keys near Main Gate','Found a set of bike keys with a blue keychain near the main gate security cabin.','Main Gate',28.6148,77.2096,'2025-04-03','09:00:00','9000000001','What color is the keychain?','Blue','active');

INSERT INTO claims
(report_id,claimant_id,secret_answer,answer_correct,verification_code,reporter_confirmed,claimant_confirmed,status) VALUES
(1,2,'Emergency: 9000000000',1,'A3F7KX92',0,0,'code_sent'),
(3,4,'Red sticker',1,'B9P2QR45',0,0,'code_sent'),
(5,7,'B-204',1,'C1M8NT67',1,1,'verified'),
(6,3,'Library Card',1,'D4W5YU23',1,1,'verified'),
(2,1,'OnePlus',1,'E6Z3VB89',0,0,'pending'),
(4,5,'Brown',1,'F2X9LC56',0,0,'pending'),
(7,6,'Blue',0,'',0,0,'rejected'),
(8,9,'Notes',1,'G7R4KD12',0,0,'code_sent'),
(9,10,'University logo',1,'H5T1MJ34',0,0,'pending'),
(10,8,'Blue',1,'I3N6WS78',1,0,'code_sent');

INSERT INTO chat_messages
(report_id,sender_id,receiver_id,message) VALUES
(1,2,1,'Hi, I think I found your ID card near the library. Can you describe it?'),
(1,1,2,'It has my photo on front and emergency number on the back. STU001.'),
(1,2,1,'Yes that matches! I have it. When can we meet to return it?'),
(1,1,2,'Can we meet at the library entrance tomorrow at 10am?'),
(3,4,3,'I found a Dell bag in CS Lab 3. Is the red sticker a circular one?'),
(3,3,4,'Yes it is a circular red sticker with my name written on it.'),
(3,4,3,'Perfect match. I will bring it to the department office tomorrow.'),
(5,7,5,'Found keys with B-204 tag near admin block. Are these yours?'),
(5,5,7,'Yes! Those are mine. Thank you so much. How can I collect them?'),
(5,7,5,'Come to the security desk at hostel block, I have submitted them there.');

INSERT INTO notifications
(user_id,report_id,type,message,link) VALUES
(1,1,'claim','Someone has claimed your Lost ID Card report','report-detail.php?id=1'),
(2,2,'match','A potential match found for Found Phone report','report-detail.php?id=2'),
(3,3,'claim','Claim submitted for your Lost Laptop Bag','report-detail.php?id=3'),
(4,4,'response','Someone responded to your Found Wallet report','report-detail.php?id=4'),
(5,5,'resolve','Your Lost Keys report has been resolved successfully','report-detail.php?id=5'),
(6,6,'resolve','Library Card report closed. Thank you for helping!','report-detail.php?id=6'),
(7,7,'claim','A claim was submitted for your Lost iPhone report','report-detail.php?id=7'),
(8,8,'match','Possible owner found for the stationery you reported','report-detail.php?id=8'),
(9,9,'claim','Someone says they found your black hoodie','report-detail.php?id=9'),
(10,10,'chat','New message about your Found Bike Keys report','chat.php?report_id=10&with=8');

INSERT INTO comments (report_id,user_id,message) VALUES
(1,3,'I saw someone pick up an ID card near the library door this morning.'),
(2,5,'There is a lost and found box at the canteen counter too.'),
(3,6,'CS Lab 3 has a shelf near the door where left items are kept by staff.'),
(4,8,'The sports teacher also maintains a lost and found register.'),
(5,2,'Hostel warden has a spare key register, you can check there too.'),
(6,4,'Great that it was found so quickly. The parking lot needs more cameras.'),
(7,9,'iPhone has Find My feature, have you tried tracking it through iCloud?'),
(8,1,'Lecture Hall 4 cleaner also collects items, check with housekeeping.'),
(9,10,'Technical fest lost items are usually collected at the event desk.'),
(10,7,'Main gate security register also logs found items daily.');

INSERT INTO reviews
(claim_id,reviewer_id,reviewed_id,rating,comment) VALUES
(3,5,7,5,'Very honest and quick to return. Great person!'),
(3,7,5,5,'Came on time, very polite. Thank you so much!'),
(4,3,6,4,'Returned the card promptly. Trustworthy.'),
(4,6,3,5,'Very cooperative and easy to coordinate with.'),
(1,1,2,5,'Extremely helpful. Returned without any hesitation.'),
(1,2,1,4,'Quick response and easy meetup.'),
(2,4,3,5,'Professional and honest student.'),
(2,3,4,5,'Very kind and quick to respond.'),
(5,8,10,4,'Good experience overall.'),
(5,10,8,5,'Wonderful person, very trustworthy.');

INSERT INTO disputes
(claim_id,raised_by,reason,status,admin_note) VALUES
(7,7,'Claimant gave wrong answer but still insists item is theirs.','under_review','Admin reviewing chat history and claim details.'),
(9,9,'Claimant confirmed pickup but item was not actually received.','open',NULL),
(1,1,'Met for handover but received a different card than mine.','resolved','Admin verified - wrong card returned. Case reopened.'),
(2,2,'Finder not responding to messages after claim.','open',NULL),
(3,3,'Laptop bag returned but charger was missing from inside.','under_review','Admin asked claimant to respond within 24 hours.'),
(4,4,'Cash from wallet appears to be missing.','open',NULL),
(5,5,'Keys handed over were not the correct ones.','resolved','Confirmed mismatch. Reporter re-filed report.'),
(6,6,'Dispute raised in error - item confirmed received.','resolved','Closed after reporter confirmed receipt.'),
(8,8,'Claimant stopped responding after code was shared.','open',NULL),
(10,10,'Bike key does not match the bike. Wrong key returned.','under_review','Admin checking security camera footage.');

INSERT INTO matches
(lost_report_id,found_report_id,score,status) VALUES
(1,6,85,'confirmed'),
(3,8,60,'pending'),
(5,10,70,'confirmed'),
(7,2,55,'pending'),
(9,8,45,'pending'),
(1,2,30,'rejected'),
(3,10,40,'pending'),
(5,6,50,'pending'),
(7,4,35,'rejected'),
(9,10,65,'pending');
