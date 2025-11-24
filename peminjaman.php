<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Borrowing Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style-peminjaman.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="flex-grow-1" style="flex: 1 0 auto; min-height: 0;">
        <section class="hero d-flex align-items-center" id="home">
            <div class="container text-center text-white">
                <h1>Lab Borrowing Dashboard</h1>
                <p>Easily manage room and equipment borrowing</p>
            </div>
        </section>

        <main class="container my-5">
            <div class="section-title">
                <h2>Borrowing Schedule</h2>
                <p>List of rooms & equipment currently borrowed</p>
            </div>

            <div class="row g-4" id="jadwalList"></div>

            <div class="section-title mt-5">
                <h2>Borrowing Calendar</h2>
                <p>View borrowing activities in calendar format</p>
            </div>

            <div id="calendar"></div>

            <div class="section-title mt-5">
                <h2>Borrowing Form</h2>
                <p>Fill out the details to request a room or equipment</p>
            </div>

            <div class="card card-surface p-4 mb-5">
                <form id="formPinjam">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="nama" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="hp" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="tipe" required>
                                <option value="Room">Room</option>
                                <option value="Equipment">Equipment</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Room / Equipment Name</label>
                            <input type="text" class="form-control" name="objek" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Time / Duration</label>
                            <input type="text" class="form-control" name="durasi" placeholder="ex: 09:00 - 11:00">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">SubmitRequest</button>
                </form>
            </div>

        </main>

        <div class="modal fade" id="modalSuccess" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-4 text-center">
                    <h5>Your Borrow Request Has Been Submitted</h5>
                    <button class="btn btn-primary mt-3" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Dummy initial data
        let jadwal = [
            { tipe: "Room", nama: "Lab Room 1", peminjam: "Faiz", durasi: "08:00 - 10:00", status: "Borrowed" },
            { tipe: "Equipment", nama: "Projector 2", peminjam: "Lutfi", durasi: "10:00 - 12:00", status: "Borrowed" }
        ];

        let calendar;

        function loadJadwal() {
            const div = document.getElementById("jadwalList");
            div.innerHTML = "";

            jadwal.forEach(item => {
                const card = `
            <div class="col-md-4 mb-3">
                <div class="borrow-card">
                    <h4 class="borrow-title">${item.nama}</h4>
                    <p class="borrow-info">
                        ${item.tipe} — borrowed by <strong>${item.peminjam}</strong>
                    </p>
                    <p class="borrow-duration">Duration: ${item.durasi}</p>
                    <p class="borrow-status"><em>${item.status}</em></p>
                </div>
            </div>
        `;
                div.innerHTML += card;
            });
        }


        function formatDate(durasi) {
            const today = new Date().toISOString().split("T")[0];
            return today + "T" + durasi.split(" - ")[0];
        }

        function loadCalendar() {
            const calendarEl = document.getElementById('calendar');

            const events = jadwal.map(item => ({
                title: `${item.nama} - ${item.peminjam}`,
                start: formatDate(item.durasi),
                backgroundColor: "#2563eb",
                borderColor: "#1e40af"
            }));

            if (calendar) {
                calendar.removeAllEvents();
                calendar.addEventSource(events);
                return;
            }

            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 650,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: events
            });

            calendar.render();
        }

        document.getElementById("formPinjam").addEventListener("submit", function (e) {
            e.preventDefault();

            const fd = new FormData(this);

            jadwal.push({
                tipe: fd.get("tipe"),
                nama: fd.get("objek"),
                peminjam: fd.get("nama"),
                durasi: fd.get("durasi"),
                status: "Pending Approval"
            });

            this.reset();

            loadJadwal();
            loadCalendar();

            new bootstrap.Modal(document.getElementById("modalSuccess")).show();
        });

        loadJadwal();
        loadCalendar();
    </script>

</body>

</html>