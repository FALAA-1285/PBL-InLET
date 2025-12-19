<?php

require_once __DIR__ . '/database.php';


// Helper function untuk cek stok tersedia (menggantikan fn_stok_tersedia)
function getStokTersedia($id_alat) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT 
                alat.stock - COALESCE((
                    SELECT COUNT(*) 
                    FROM peminjaman 
                    WHERE id_alat = :id AND status = 'dipinjam'
                ), 0) AS stok_tersedia
            FROM alat_lab alat
            WHERE id_alat_lab = :id
        ");
        $stmt->execute(['id' => $id_alat]);
        $result = $stmt->fetch();
        return $result ? (int)$result['stok_tersedia'] : 0;
    } catch (PDOException $e) {
        return 0;
    }
}

// untuk cek konflik ruang
function cekKonflikRuang($id_ruang, $tanggal_pinjam, $waktu_pinjam, $waktu_kembali) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT COUNT(*) as cnt 
            FROM peminjaman
            WHERE id_ruang = :id_ruang
            AND status = 'dipinjam'
            AND tanggal_pinjam = :tanggal
            AND :waktu_pinjam < waktu_kembali
            AND :waktu_kembali > waktu_pinjam
        ");
        $stmt->execute([
            'id_ruang' => $id_ruang,
            'tanggal' => $tanggal_pinjam,
            'waktu_pinjam' => $waktu_pinjam,
            'waktu_kembali' => $waktu_kembali
        ]);
        $result = $stmt->fetch();
        return ($result['cnt'] ?? 0) > 0;
    } catch (PDOException $e) {
        return false;
    }
}

//  Call create_request procedure
function callCreateRequest($p_id_alat, $p_id_ruang, $p_nama_peminjam, $p_tanggal_pinjam, $p_waktu_pinjam = null, $p_waktu_kembali = null, $p_keterangan = null, $p_jumlah = 1) {
    try {
        $conn = getDBConnection();
        
        // Use DO block with temporary table to get OUT parameters
        $conn->exec("
        DO \$\$
        DECLARE
            v_id_request INTEGER;
            v_result_code INTEGER;
            v_result_message VARCHAR;
        BEGIN
            CALL public.create_request(
                " . ($p_id_alat ?: 'NULL') . "::INTEGER, 
                " . ($p_id_ruang ?: 'NULL') . "::INTEGER, 
                " . ($p_nama_peminjam ? "'" . str_replace("'", "''", $p_nama_peminjam) . "'" : 'NULL') . "::VARCHAR, 
                '" . $p_tanggal_pinjam . "'::DATE, 
                " . ($p_waktu_pinjam ? "'" . $p_waktu_pinjam . "'" : 'NULL') . "::TIME, 
                " . ($p_waktu_kembali ? "'" . $p_waktu_kembali . "'" : 'NULL') . "::TIME, 
                " . ($p_keterangan ? "'" . str_replace("'", "''", $p_keterangan) . "'" : 'NULL') . "::VARCHAR, 
                " . $p_jumlah . "::INTEGER,
                v_id_request, v_result_code, v_result_message
            );
            
            -- Store in temp table
            CREATE TEMP TABLE IF NOT EXISTS temp_request_result (
                id_request INTEGER,
                result_code INTEGER,
                result_message VARCHAR
            );
            DELETE FROM temp_request_result;
            INSERT INTO temp_request_result VALUES (v_id_request, v_result_code, v_result_message);
        END \$\$;
        ");
        
        // Get results from temp table
        $result = $conn->query("SELECT * FROM temp_request_result")->fetch();
        
        if ($result) {
            return [
                'success' => $result['result_code'] > 0,
                'id_request' => $result['id_request'],
                'code' => $result['result_code'],
                'message' => $result['result_message']
            ];
        }
        
        return [
            'success' => false,
            'id_request' => null,
            'code' => -999,
            'message' => 'Failed to get procedure result'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'id_request' => null,
            'code' => -999,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

// Call proc_update_absensi procedure
function callUpdateAbsensi($p_nim, $p_action, $p_keterangan = null) {
    try {
        $conn = getDBConnection();
        
        // Use DO block to call procedure and get results
        $sql = "
        DO \$\$
        DECLARE
            v_id_absensi INTEGER;
            v_result_code INTEGER;
            v_result_message VARCHAR;
        BEGIN
            CALL public.proc_update_absensi(
                :p_nim, :p_action, :p_keterangan,
                v_id_absensi, v_result_code, v_result_message
            );
            -- Store results in temporary table
            CREATE TEMP TABLE IF NOT EXISTS temp_absensi_result (
                id_absensi INTEGER,
                result_code INTEGER,
                result_message VARCHAR
            );
            DELETE FROM temp_absensi_result;
            INSERT INTO temp_absensi_result VALUES (v_id_absensi, v_result_code, v_result_message);
        END \$\$;
        ";
        
        // Better: Use function wrapper or direct query
        // For now, we'll use a simpler approach with a wrapper function
        return callProcUpdateAbsensiDirect($p_nim, $p_action, $p_keterangan);
    } catch (PDOException $e) {
        return [
            'success' => false,
            'id_absensi' => null,
            'code' => -999,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

// implementasi proc_update_absensi logic
function callProcUpdateAbsensiDirect($p_nim, $p_action, $p_keterangan = null) {
    try {
        $conn = getDBConnection();
        
        // Detect which column exists in absensi table
        $check_cols = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'absensi' AND table_schema = 'public'");
        $columns = $check_cols->fetchAll(PDO::FETCH_COLUMN);
        $student_col = null;
        if (in_array('id_mahasiswa', $columns)) {
            $student_col = 'id_mahasiswa';
        } elseif (in_array('nim', $columns)) {
            $student_col = 'nim';
        } elseif (in_array('id_mhs', $columns)) {
            $student_col = 'id_mhs';
        }
        
        if (!$student_col) {
            return [
                'success' => false,
                'id_absensi' => null,
                'code' => -99,
                'message' => 'Tabel absensi tidak memiliki kolom identifier mahasiswa'
            ];
        }
        
        // Validate mahasiswa exists - use id_mahasiswa instead of nim
        $stmt = $conn->prepare("SELECT 1 FROM mahasiswa WHERE id_mahasiswa = :nim");
        $stmt->execute(['nim' => $p_nim]);
        if (!$stmt->fetch()) {
            return [
                'success' => false,
                'id_absensi' => null,
                'code' => -1,
                'message' => 'Mahasiswa tidak ditemukan'
            ];
        }
        
        // Validate action
        if (!in_array($p_action, ['checkin', 'checkout'])) {
            return [
                'success' => false,
                'id_absensi' => null,
                'code' => -2,
                'message' => 'Action harus checkin atau checkout'
            ];
        }
        
        // Get existing absensi
        $stmt = $conn->prepare("SELECT * FROM absensi WHERE " . $student_col . " = :nim AND CAST(tanggal AS DATE) = CAST(CURRENT_DATE AS DATE)");
        $stmt->execute(['nim' => $p_nim]);
        $v_absensi_hari_ini = $stmt->fetch();
        
        if ($p_action === 'checkin') {
            if ($v_absensi_hari_ini && $v_absensi_hari_ini['waktu_datang']) {
                return [
                    'success' => false,
                    'id_absensi' => $v_absensi_hari_ini['id_absensi'],
                    'code' => -3,
                    'message' => 'Sudah check in hari ini'
                ];
            }
            
            if ($v_absensi_hari_ini) {
                $stmt = $conn->prepare("UPDATE absensi SET waktu_datang = CURRENT_TIMESTAMP, keterangan = COALESCE(:keterangan, keterangan) WHERE id_absensi = :id");
                $stmt->execute(['keterangan' => $p_keterangan, 'id' => $v_absensi_hari_ini['id_absensi']]);
                $id_absensi = $v_absensi_hari_ini['id_absensi'];
            } else {
                // Dynamically use the detected column - use CURRENT_DATE from PostgreSQL
                $stmt = $conn->prepare("INSERT INTO absensi (" . $student_col . ", tanggal, waktu_datang, keterangan) VALUES (:nim, CURRENT_DATE, CURRENT_TIMESTAMP, :keterangan) RETURNING id_absensi");
                $stmt->execute(['nim' => $p_nim, 'keterangan' => $p_keterangan]);
                $id_absensi = $stmt->fetchColumn();
            }
            
            return [
                'success' => true,
                'id_absensi' => $id_absensi,
                'code' => 1,
                'message' => 'Check in berhasil'
            ];
            
        } elseif ($p_action === 'checkout') {
            if (!$v_absensi_hari_ini || !$v_absensi_hari_ini['waktu_datang']) {
                return [
                    'success' => false,
                    'id_absensi' => null,
                    'code' => -4,
                    'message' => 'Belum check in hari ini'
                ];
            }
            
            if ($v_absensi_hari_ini['waktu_pulang']) {
                return [
                    'success' => false,
                    'id_absensi' => $v_absensi_hari_ini['id_absensi'],
                    'code' => -5,
                    'message' => 'Sudah check out hari ini'
                ];
            }
            
            $stmt = $conn->prepare("UPDATE absensi SET waktu_pulang = CURRENT_TIMESTAMP, keterangan = COALESCE(:keterangan, keterangan) WHERE id_absensi = :id");
            $stmt->execute(['keterangan' => $p_keterangan, 'id' => $v_absensi_hari_ini['id_absensi']]);
            
            return [
                'success' => true,
                'id_absensi' => $v_absensi_hari_ini['id_absensi'],
                'code' => 1,
                'message' => 'Check out berhasil'
            ];
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'id_absensi' => null,
            'code' => -999,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

// Call proc_return_peminjaman procedure
function callReturnPeminjaman($p_id_peminjaman, $p_id_admin_return, $p_kondisi_barang = 'baik', $p_catatan_return = null) {
    try {
        $conn = getDBConnection();
        
        // Use DO block to call procedure and get OUT parameters
        $sql = "
        DO \$\$
        DECLARE
            v_result_code INTEGER;
            v_result_message VARCHAR;
        BEGIN
            CALL public.proc_return_peminjaman(
                v_result_code, v_result_message,
                " . intval($p_id_peminjaman) . "::INTEGER,
                " . ($p_id_admin_return ? intval($p_id_admin_return) : 'NULL') . "::INTEGER,
                '" . str_replace("'", "''", $p_kondisi_barang) . "'::VARCHAR,
                " . ($p_catatan_return ? "'" . str_replace("'", "''", $p_catatan_return) . "'" : 'NULL') . "::TEXT
            );
            
            -- Store in temp table
            CREATE TEMP TABLE IF NOT EXISTS temp_return_result (
                result_code INTEGER,
                result_message VARCHAR
            );
            DELETE FROM temp_return_result;
            INSERT INTO temp_return_result VALUES (v_result_code, v_result_message);
        END \$\$;
        ";
        
        $conn->exec($sql);
        
        // Get results from temp table
        $result = $conn->query("SELECT * FROM temp_return_result")->fetch();
        
        if ($result) {
            return [
                'success' => $result['result_code'] > 0,
                'code' => $result['result_code'],
                'message' => $result['result_message']
            ];
        }
        
        // Fallback to direct implementation if procedure call fails
        return callReturnPeminjamanDirect($p_id_peminjaman, $p_id_admin_return, $p_kondisi_barang, $p_catatan_return);
    } catch (PDOException $e) {
        // Fallback to direct implementation on error
        return callReturnPeminjamanDirect($p_id_peminjaman, $p_id_admin_return, $p_kondisi_barang, $p_catatan_return);
    }
}

// Direct implementation of proc_return_peminjaman
function callReturnPeminjamanDirect($p_id_peminjaman, $p_id_admin_return, $p_kondisi_barang = 'baik', $p_catatan_return = null) {
    try {
        $conn = getDBConnection();
        
        // Get peminjaman data
        $stmt = $conn->prepare("SELECT * FROM peminjaman WHERE id_peminjaman = :id AND status = 'dipinjam'");
        $stmt->execute(['id' => $p_id_peminjaman]);
        $v_peminjaman = $stmt->fetch();
        
        if (!$v_peminjaman) {
            return [
                'success' => false,
                'code' => -1,
                'message' => 'Peminjaman tidak ditemukan atau sudah dikembalikan'
            ];
        }
        
        // Ensure tanggal_kembali >= tanggal_pinjam to satisfy constraint
        $tanggal_hari_ini = date('Y-m-d');
        $tanggal_pinjam = $v_peminjaman['tanggal_pinjam'];
        // Use max of today and borrow date to ensure tanggal_kembali >= tanggal_pinjam
        $tanggal_kembali = $tanggal_pinjam > $tanggal_hari_ini ? $tanggal_pinjam : $tanggal_hari_ini;
        
        // Check if history_pengembalian table exists, if not skip insert
        try {
            $stmt = $conn->prepare("INSERT INTO history_pengembalian (
                id_peminjaman, id_alat, id_ruang, nama_peminjam,
                tanggal_pinjam, tanggal_kembali, waktu_pinjam, waktu_kembali,
                keterangan, id_admin_return, tanggal_return,
                kondisi_barang, catatan_return
            ) VALUES (
                :id_peminjaman, :id_alat, :id_ruang, :nama_peminjam,
                :tanggal_pinjam, :tanggal_kembali, :waktu_pinjam, :waktu_kembali,
                :keterangan, :id_admin_return, CURRENT_TIMESTAMP,
                :kondisi_barang, :catatan_return
            )");
            $stmt->execute([
                'id_peminjaman' => $v_peminjaman['id_peminjaman'],
                'id_alat' => $v_peminjaman['id_alat'],
                'id_ruang' => $v_peminjaman['id_ruang'],
                'nama_peminjam' => $v_peminjaman['nama_peminjam'],
                'tanggal_pinjam' => $v_peminjaman['tanggal_pinjam'],
                'tanggal_kembali' => $tanggal_kembali,
                'waktu_pinjam' => $v_peminjaman['waktu_pinjam'],
                'waktu_kembali' => $v_peminjaman['waktu_kembali'],
                'keterangan' => $v_peminjaman['keterangan'],
                'id_admin_return' => $p_id_admin_return,
                'kondisi_barang' => $p_kondisi_barang,
                'catatan_return' => $p_catatan_return
            ]);
        } catch (PDOException $e) {
            // Table might not exist, continue without history
        }
        
        // Update peminjaman status
        $stmt = $conn->prepare("UPDATE peminjaman SET status = 'dikembalikan', tanggal_kembali = :tanggal_kembali WHERE id_peminjaman = :id");
        $stmt->execute([
            'id' => $p_id_peminjaman,
            'tanggal_kembali' => $tanggal_kembali
        ]);
        
        return [
            'success' => true,
            'code' => 1,
            'message' => 'Pengembalian berhasil diproses'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'code' => -999,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

// Call proc_reject_request procedure
function callRejectRequest($p_id_request, $p_id_admin, $p_alasan_reject) {
    try {
        $conn = getDBConnection();
        
        // Use DO block to call procedure and get OUT parameters
        $sql = "
        DO \$\$
        DECLARE
            v_result_code INTEGER;
            v_result_message VARCHAR;
        BEGIN
            CALL public.proc_reject_request(
                " . intval($p_id_request) . "::INTEGER,
                " . ($p_id_admin ? intval($p_id_admin) : 'NULL') . "::INTEGER,
                '" . str_replace("'", "''", $p_alasan_reject) . "'::TEXT,
                v_result_code, v_result_message
            );
            
            -- Store in temp table
            CREATE TEMP TABLE IF NOT EXISTS temp_reject_result (
                result_code INTEGER,
                result_message VARCHAR
            );
            DELETE FROM temp_reject_result;
            INSERT INTO temp_reject_result VALUES (v_result_code, v_result_message);
        END \$\$;
        ";
        
        $conn->exec($sql);
        
        // Get results from temp table
        $result = $conn->query("SELECT * FROM temp_reject_result")->fetch();
        
        if ($result) {
            return [
                'success' => $result['result_code'] > 0,
                'code' => $result['result_code'],
                'message' => $result['result_message']
            ];
        }
        
        // Fallback if procedure call fails
        return [
            'success' => false,
            'code' => -999,
            'message' => 'Failed to get procedure result'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'code' => -999,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

// Approve request and create peminjaman record
function callApproveRequest($p_id_request, $p_id_admin) {
    try {
        $conn = getDBConnection();
        
        // Get request data
        $stmt = $conn->prepare("SELECT * FROM request_peminjaman WHERE id_request = :id AND status = 'pending'");
        $stmt->execute(['id' => $p_id_request]);
        $v_request = $stmt->fetch();
        
        if (!$v_request) {
            return [
                'success' => false,
                'code' => -1,
                'message' => 'Request tidak ditemukan atau sudah diproses'
            ];
        }
        
        // Start transaction
        $conn->beginTransaction();
        
        try {
            // Insert into peminjaman table
            if ($v_request['id_alat']) {
                // Tool borrowing
                $stmt = $conn->prepare("INSERT INTO peminjaman (id_alat, nama_peminjam, tanggal_pinjam, status, keterangan, waktu_pinjam, waktu_kembali) 
                    VALUES (:id_alat, :nama_peminjam, :tanggal_pinjam, 'dipinjam', :keterangan, :waktu_pinjam, :waktu_kembali)");
                $stmt->execute([
                    'id_alat' => $v_request['id_alat'],
                    'nama_peminjam' => $v_request['nama_peminjam'],
                    'tanggal_pinjam' => $v_request['tanggal_pinjam'],
                    'keterangan' => $v_request['keterangan'],
                    'waktu_pinjam' => $v_request['waktu_pinjam'],
                    'waktu_kembali' => $v_request['waktu_kembali']
                ]);
            } elseif ($v_request['id_ruang']) {
                // Room borrowing
                // id_alat is NOT NULL, so we use 0 as placeholder for room borrowing
                $stmt = $conn->prepare("INSERT INTO peminjaman (id_alat, id_ruang, nama_peminjam, tanggal_pinjam, status, keterangan, waktu_pinjam, waktu_kembali) 
                    VALUES (0, :id_ruang, :nama_peminjam, :tanggal_pinjam, 'dipinjam', :keterangan, :waktu_pinjam, :waktu_kembali)");
                $stmt->execute([
                    'id_ruang' => $v_request['id_ruang'],
                    'nama_peminjam' => $v_request['nama_peminjam'],
                    'tanggal_pinjam' => $v_request['tanggal_pinjam'],
                    'keterangan' => $v_request['keterangan'],
                    'waktu_pinjam' => $v_request['waktu_pinjam'],
                    'waktu_kembali' => $v_request['waktu_kembali']
                ]);
            }
            
            // Update request status to approved
            $stmt = $conn->prepare("UPDATE request_peminjaman SET status = 'approved', id_admin_approve = :id_admin, tanggal_approve = CURRENT_TIMESTAMP WHERE id_request = :id");
            $stmt->execute([
                'id' => $p_id_request,
                'id_admin' => $p_id_admin
            ]);
            
            // Commit transaction
            $conn->commit();
            
            return [
                'success' => true,
                'code' => 1,
                'message' => 'Request berhasil disetujui'
            ];
        } catch (PDOException $e) {
            // Rollback on error
            $conn->rollBack();
            throw $e;
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'code' => -999,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

