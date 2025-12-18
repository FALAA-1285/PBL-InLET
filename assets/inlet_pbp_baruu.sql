--
-- PostgreSQL database dump
--

<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
\restrict 9HCm3mc6FpSrfS1QiZsTXQoIiIxVBcSMLQU3Udc4sAOPL7lRY8bRh4hXPv59Kaz
=======
\restrict YM77hedTVLLAKQbTcEBGAv7TbB8ZUWNRV6xnZDqjWtocHGsfv2DPogfn4K6jPov
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
\restrict bpSimOsKLgSBDNW46QyuKpgMphnhkBYC8p74cGPgSMP93pGj6QJdGCrdtIA3CNe
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql

-- Dumped from database version 15.14
-- Dumped by pg_dump version 15.14

<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- Started on 2025-12-04 08:46:38

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 235 (class 1259 OID 35226)
-- Name: absensi; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.absensi (
    id_absensi integer NOT NULL,
    nim character varying(20) NOT NULL,
    waktu_datang timestamp without time zone,
    waktu_pulang timestamp without time zone,
    keterangan text,
    tanggal date DEFAULT CURRENT_DATE NOT NULL
);


ALTER TABLE public.absensi OWNER TO postgres;

--
-- TOC entry 234 (class 1259 OID 35225)
-- Name: absensi_id_absensi_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.absensi_id_absensi_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.absensi_id_absensi_seq OWNER TO postgres;

--
-- TOC entry 3593 (class 0 OID 0)
-- Dependencies: 234
-- Name: absensi_id_absensi_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.absensi_id_absensi_seq OWNED BY public.absensi.id_absensi;


--
-- TOC entry 219 (class 1259 OID 35149)
-- Name: admin; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.admin (
    id_admin integer NOT NULL,
    username character varying(100) NOT NULL,
    password_hash character varying(255) NOT NULL,
    role character varying(50) DEFAULT 'user'::character varying,
    created_at timestamp with time zone DEFAULT now()
);


ALTER TABLE public.admin OWNER TO postgres;

--
-- TOC entry 218 (class 1259 OID 35148)
-- Name: admin_id_admin_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.admin_id_admin_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.admin_id_admin_seq OWNER TO postgres;

--
-- TOC entry 3594 (class 0 OID 0)
-- Dependencies: 218
-- Name: admin_id_admin_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.admin_id_admin_seq OWNED BY public.admin.id_admin;


--
-- TOC entry 237 (class 1259 OID 35236)
-- Name: alat_lab; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.alat_lab (
    id_alat_lab integer NOT NULL,
    nama_alat character varying(255) NOT NULL,
    deskripsi text,
    stock integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    id_admin integer
);


ALTER TABLE public.alat_lab OWNER TO postgres;

--
-- TOC entry 236 (class 1259 OID 35235)
-- Name: alat_lab_id_alat_lab_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.alat_lab_id_alat_lab_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.alat_lab_id_alat_lab_seq OWNER TO postgres;

--
-- TOC entry 3595 (class 0 OID 0)
-- Dependencies: 236
-- Name: alat_lab_id_alat_lab_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.alat_lab_id_alat_lab_seq OWNED BY public.alat_lab.id_alat_lab;


--
-- TOC entry 229 (class 1259 OID 35197)
-- Name: artikel; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.artikel (
    id_artikel integer NOT NULL,
    judul character varying(255) NOT NULL,
    tahun integer,
    konten character varying(4000)
);


ALTER TABLE public.artikel OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 35196)
-- Name: artikel_id_artikel_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.artikel_id_artikel_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.artikel_id_artikel_seq OWNER TO postgres;

--
-- TOC entry 3596 (class 0 OID 0)
-- Dependencies: 228
-- Name: artikel_id_artikel_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.artikel_id_artikel_seq OWNED BY public.artikel.id_artikel;


--
-- TOC entry 227 (class 1259 OID 35187)
-- Name: berita; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.berita (
    id_berita integer NOT NULL,
    judul character varying(255) NOT NULL,
    konten character varying(4000),
    gambar_thumbnail character varying(255),
    created_at timestamp with time zone DEFAULT now(),
    id_admin integer
);


ALTER TABLE public.berita OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 35186)
-- Name: berita_id_berita_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.berita_id_berita_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.berita_id_berita_seq OWNER TO postgres;

--
-- TOC entry 3597 (class 0 OID 0)
-- Dependencies: 226
-- Name: berita_id_berita_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.berita_id_berita_seq OWNED BY public.berita.id_berita;


--
-- TOC entry 217 (class 1259 OID 35135)
-- Name: buku_tamu; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.buku_tamu (
    id_buku_tamu integer NOT NULL,
    nama character varying(150) NOT NULL,
    email character varying(150) NOT NULL,
    institusi character varying(200) NOT NULL,
    no_hp character varying(50) NOT NULL,
    pesan character varying(2000),
    created_at timestamp with time zone DEFAULT now(),
    is_read boolean DEFAULT false,
    admin_response character varying(2000)
);


ALTER TABLE public.buku_tamu OWNER TO postgres;

--
-- TOC entry 216 (class 1259 OID 35134)
-- Name: buku_tamu_id_buku_tamu_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.buku_tamu_id_buku_tamu_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.buku_tamu_id_buku_tamu_seq OWNER TO postgres;

--
-- TOC entry 3598 (class 0 OID 0)
-- Dependencies: 216
-- Name: buku_tamu_id_buku_tamu_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.buku_tamu_id_buku_tamu_seq OWNED BY public.buku_tamu.id_buku_tamu;


--
-- TOC entry 254 (class 1259 OID 42428)
-- Name: contact_info; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.contact_info (
    id_contact integer NOT NULL,
    contact_email character varying(255),
    contact_phone character varying(100),
    contact_address character varying(255)
);


ALTER TABLE public.contact_info OWNER TO postgres;

--
-- TOC entry 253 (class 1259 OID 42427)
-- Name: contact_info_id_contact_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.contact_info_id_contact_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.contact_info_id_contact_seq OWNER TO postgres;

--
-- TOC entry 3599 (class 0 OID 0)
-- Dependencies: 253
-- Name: contact_info_id_contact_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.contact_info_id_contact_seq OWNED BY public.contact_info.id_contact;


--
-- TOC entry 243 (class 1259 OID 35269)
-- Name: fokus_penelitian; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fokus_penelitian (
    id_fp integer NOT NULL,
    title character varying(200) NOT NULL,
    deskripsi text,
    detail character varying(150) NOT NULL
);


ALTER TABLE public.fokus_penelitian OWNER TO postgres;

--
-- TOC entry 242 (class 1259 OID 35268)
-- Name: fokus_penelitian_id_fp_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fokus_penelitian_id_fp_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fokus_penelitian_id_fp_seq OWNER TO postgres;

--
-- TOC entry 3600 (class 0 OID 0)
-- Dependencies: 242
-- Name: fokus_penelitian_id_fp_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fokus_penelitian_id_fp_seq OWNED BY public.fokus_penelitian.id_fp;


--
-- TOC entry 252 (class 1259 OID 42419)
-- Name: footer_settings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.footer_settings (
    id_footer integer NOT NULL,
    footer_logo character varying(255),
    footer_title character varying(255),
    footer_subtitle character varying(255),
    copyright_text text
);


ALTER TABLE public.footer_settings OWNER TO postgres;

--
-- TOC entry 251 (class 1259 OID 42418)
-- Name: footer_settings_id_footer_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.footer_settings_id_footer_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.footer_settings_id_footer_seq OWNER TO postgres;

--
-- TOC entry 3601 (class 0 OID 0)
-- Dependencies: 251
-- Name: footer_settings_id_footer_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.footer_settings_id_footer_seq OWNED BY public.footer_settings.id_footer;


--
-- TOC entry 215 (class 1259 OID 35122)
-- Name: gallery; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.gallery (
    id_gallery integer NOT NULL,
    id_berita integer,
    gambar character varying(500),
    judul character varying(255),
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now()
);


ALTER TABLE public.gallery OWNER TO postgres;

--
-- TOC entry 214 (class 1259 OID 35121)
-- Name: gallery_id_gallery_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.gallery_id_gallery_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.gallery_id_gallery_seq OWNER TO postgres;

--
-- TOC entry 3602 (class 0 OID 0)
-- Dependencies: 214
-- Name: gallery_id_gallery_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.gallery_id_gallery_seq OWNED BY public.gallery.id_gallery;


--
-- TOC entry 221 (class 1259 OID 35160)
-- Name: mahasiswa; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mahasiswa (
    nim character varying(20) NOT NULL,
    nama character varying(150) NOT NULL,
    tahun integer,
    status character varying(20) DEFAULT 'regular'::character varying NOT NULL,
    id_admin integer,
    CONSTRAINT chk_mahasiswa_status CHECK (((status)::text = ANY ((ARRAY['magang'::character varying, 'skripsi'::character varying, 'regular'::character varying])::text[])))
);


ALTER TABLE public.mahasiswa OWNER TO postgres;

--
-- TOC entry 220 (class 1259 OID 35159)
-- Name: mahasiswa_id_mahasiswa_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.mahasiswa_id_mahasiswa_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mahasiswa_id_mahasiswa_seq OWNER TO postgres;

--
-- TOC entry 3603 (class 0 OID 0)
-- Dependencies: 220
-- Name: mahasiswa_id_mahasiswa_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.mahasiswa_id_mahasiswa_seq OWNED BY public.mahasiswa.nim;


--
-- TOC entry 223 (class 1259 OID 35169)
-- Name: member; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.member (
    id_member integer NOT NULL,
    nama character varying(150) NOT NULL,
    email character varying(150),
    jabatan character varying(100),
    foto character varying(255),
    bidang_keahlian character varying(255),
    notlp character varying(30),
    deskripsi text,
    alamat text,
    id_admin integer
);


ALTER TABLE public.member OWNER TO postgres;

--
-- TOC entry 222 (class 1259 OID 35168)
-- Name: member_id_member_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.member_id_member_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.member_id_member_seq OWNER TO postgres;

--
-- TOC entry 3604 (class 0 OID 0)
-- Dependencies: 222
-- Name: member_id_member_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.member_id_member_seq OWNED BY public.member.id_member;


--
-- TOC entry 225 (class 1259 OID 35178)
-- Name: mitra; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mitra (
    id_mitra integer NOT NULL,
    nama_institusi character varying(255) NOT NULL,
    logo character varying(255)
);


ALTER TABLE public.mitra OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 35177)
-- Name: mitra_id_mitra_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.mitra_id_mitra_seq
=======
-- Started on 2025-12-04 09:36:32
=======
-- Started on 2025-12-14 21:23:39
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 268 (class 1255 OID 46818)
-- Name: create_request(integer, integer, character varying, date, time without time zone, time without time zone, character varying, integer); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.create_request(IN p_id_alat integer, IN p_id_ruang integer, IN p_nama_peminjam character varying, IN p_tanggal_pinjam date, IN p_waktu_pinjam time without time zone, IN p_waktu_kembali time without time zone, IN p_keterangan character varying, IN p_jumlah integer, OUT p_id_request integer, OUT p_result_code integer, OUT p_result_message character varying)
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_stock_available INTEGER;
BEGIN
    --------------------------------------------------
    -- Default Handling
    --------------------------------------------------
    IF p_keterangan IS NULL THEN p_keterangan := ''; END IF;
    IF p_jumlah IS NULL THEN p_jumlah := 1; END IF;

    --------------------------------------------------
    -- Validate pilihan alat/ruang
    --------------------------------------------------
    IF (p_id_alat IS NULL AND p_id_ruang IS NULL) THEN
        p_result_code := -1;
        p_result_message := 'Harus memilih alat atau ruang';
        RETURN;
    END IF;

    IF (p_id_alat IS NOT NULL AND p_id_ruang IS NOT NULL) THEN
        p_result_code := -2;
        p_result_message := 'Tidak bisa meminjam alat dan ruang bersamaan';
        RETURN;
    END IF;

    --------------------------------------------------
    -- Validate nama
    --------------------------------------------------
    IF p_nama_peminjam IS NULL OR TRIM(p_nama_peminjam) = '' THEN
        p_result_code := -3;
        p_result_message := 'Nama peminjam harus diisi';
        RETURN;
    END IF;

    --------------------------------------------------
    -- Validate tanggal
    --------------------------------------------------
    IF p_tanggal_pinjam IS NULL THEN
        p_result_code := -4;
        p_result_message := 'Tanggal pinjam harus diisi';
        RETURN;
    END IF;

    IF p_tanggal_pinjam < CURRENT_DATE THEN
        p_result_code := -5;
        p_result_message := 'Tanggal pinjam tidak boleh di masa lalu';
        RETURN;
    END IF;

    --------------------------------------------------
    -- Validate jumlah
    --------------------------------------------------
    IF p_jumlah <= 0 THEN
        p_result_code := -6;
        p_result_message := 'Jumlah harus lebih dari 0';
        RETURN;
    END IF;

    --------------------------------------------------
    -- Validate alat
    --------------------------------------------------
    IF p_id_alat IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1 FROM public.alat_lab WHERE id_alat_lab = p_id_alat
        ) THEN
            p_result_code := -7;
            p_result_message := 'Alat tidak ditemukan';
            RETURN;
        END IF;

        SELECT fn_stok_tersedia(p_id_alat)
        INTO v_stock_available;

        IF v_stock_available < p_jumlah THEN
            p_result_code := -8;
            p_result_message := 'Stok tidak mencukupi. Stok tersedia: ' || v_stock_available;
            RETURN;
        END IF;
    END IF;

    --------------------------------------------------
    -- Validate ruang
    --------------------------------------------------
    IF p_id_ruang IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1 FROM public.ruang_lab WHERE id_ruang_lab = p_id_ruang
        ) THEN
            p_result_code := -9;
            p_result_message := 'Ruangan tidak ditemukan';
            RETURN;
        END IF;

        IF fn_cek_konflik_ruang(p_id_ruang, p_tanggal_pinjam, p_waktu_pinjam, p_waktu_kembali) THEN
            p_result_code := -10;
            p_result_message := 'Ruangan sudah dipinjam pada waktu tersebut';
            RETURN;
        END IF;
    END IF;

    --------------------------------------------------
    -- Insert request
    --------------------------------------------------
    INSERT INTO public.request_peminjaman (
        id_alat, id_ruang, nama_peminjam, tanggal_pinjam,
        waktu_pinjam, waktu_kembali, keterangan, jumlah, status
    )
    VALUES (
        p_id_alat, p_id_ruang, p_nama_peminjam, p_tanggal_pinjam,
        p_waktu_pinjam, p_waktu_kembali, p_keterangan, p_jumlah, 'pending'
    )
    RETURNING id_request INTO p_id_request;

    p_result_code := 1;
    p_result_message := 'Request berhasil dibuat';
END;
$$;


ALTER PROCEDURE public.create_request(IN p_id_alat integer, IN p_id_ruang integer, IN p_nama_peminjam character varying, IN p_tanggal_pinjam date, IN p_waktu_pinjam time without time zone, IN p_waktu_kembali time without time zone, IN p_keterangan character varying, IN p_jumlah integer, OUT p_id_request integer, OUT p_result_code integer, OUT p_result_message character varying) OWNER TO postgres;

--
-- TOC entry 280 (class 1255 OID 46819)
-- Name: proc_reject_request(integer, integer, text); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.proc_reject_request(IN p_id_request integer, IN p_id_admin integer, IN p_alasan_reject text, OUT p_result_code integer, OUT p_result_message character varying)
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_request RECORD;
BEGIN
    -- Get request data
    SELECT * INTO v_request
    FROM public.request_peminjaman
    WHERE id_request = p_id_request AND status = 'pending';
    
    -- Check if request exists and is still pending
    IF v_request IS NULL THEN
        p_result_code := -1;
        p_result_message := 'Request tidak ditemukan atau sudah diproses';
        RETURN;
    END IF;
    
    -- Validate alasan reject
    IF p_alasan_reject IS NULL OR TRIM(p_alasan_reject) = '' THEN
        p_result_code := -2;
        p_result_message := 'Alasan reject harus diisi';
        RETURN;
    END IF;
    
    -- Update request status to rejected
    UPDATE public.request_peminjaman
    SET status = 'rejected',
        id_admin_approve = p_id_admin,
        tanggal_approve = CURRENT_TIMESTAMP,
        alasan_reject = p_alasan_reject
    WHERE id_request = p_id_request;
    
    p_result_code := 1;
    p_result_message := 'Request berhasil ditolak';
END;
$$;


ALTER PROCEDURE public.proc_reject_request(IN p_id_request integer, IN p_id_admin integer, IN p_alasan_reject text, OUT p_result_code integer, OUT p_result_message character varying) OWNER TO postgres;

--
-- TOC entry 281 (class 1255 OID 46820)
-- Name: proc_return_peminjaman(integer, integer, character varying, text); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.proc_return_peminjaman(OUT p_result_code integer, OUT p_result_message character varying, IN p_id_peminjaman integer, IN p_id_admin_return integer, IN p_kondisi_barang character varying DEFAULT 'baik'::character varying, IN p_catatan_return text DEFAULT NULL::text)
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_peminjaman RECORD;
    v_tanggal_kembali date;
BEGIN
    -- Get peminjaman data
    SELECT * INTO v_peminjaman
    FROM public.peminjaman
    WHERE id_peminjaman = p_id_peminjaman
      AND status = 'dipinjam';
    
    IF v_peminjaman IS NULL THEN
        p_result_code := -1;
        p_result_message := 'Peminjaman tidak ditemukan atau sudah dikembalikan';
        RETURN;
    END IF;

    v_tanggal_kembali := CURRENT_DATE;

    INSERT INTO public.history_pengembalian (
        id_peminjaman, id_alat, id_ruang, nama_peminjam,
        tanggal_pinjam, tanggal_kembali, waktu_pinjam, waktu_kembali,
        keterangan, id_admin_return, tanggal_return,
        kondisi_barang, catatan_return
    )
    VALUES (
        v_peminjaman.id_peminjaman, v_peminjaman.id_alat, v_peminjaman.id_ruang,
        v_peminjaman.nama_peminjam, v_peminjaman.tanggal_pinjam, v_tanggal_kembali,
        v_peminjaman.waktu_pinjam, v_peminjaman.waktu_kembali,
        v_peminjaman.keterangan, p_id_admin_return, CURRENT_TIMESTAMP,
        p_kondisi_barang, p_catatan_return
    );

    UPDATE public.peminjaman
    SET status = 'dikembalikan',
        tanggal_kembali = v_tanggal_kembali
    WHERE id_peminjaman = p_id_peminjaman;

    p_result_code := 1;
    p_result_message := 'Pengembalian berhasil diproses';
END;
$$;


ALTER PROCEDURE public.proc_return_peminjaman(OUT p_result_code integer, OUT p_result_message character varying, IN p_id_peminjaman integer, IN p_id_admin_return integer, IN p_kondisi_barang character varying, IN p_catatan_return text) OWNER TO postgres;

--
-- TOC entry 282 (class 1255 OID 46821)
-- Name: proc_update_absensi(character varying, character varying, text); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.proc_update_absensi(IN p_nim character varying, IN p_action character varying, IN p_keterangan text, OUT p_id_absensi integer, OUT p_result_code integer, OUT p_result_message character varying)
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_absensi_hari_ini RECORD;
    v_tanggal_hari_ini date;
BEGIN
    -- Validate mahasiswa exists
    IF NOT EXISTS (SELECT 1 FROM public.mahasiswa WHERE nim = p_nim) THEN
        p_result_code := -1;
        p_result_message := 'Mahasiswa tidak ditemukan';
        RETURN;
    END IF;

    -- Validate action
    IF p_action NOT IN ('checkin', 'checkout') THEN
        p_result_code := -2;
        p_result_message := 'Action harus checkin atau checkout';
        RETURN;
    END IF;

    v_tanggal_hari_ini := CURRENT_DATE;

    SELECT * INTO v_absensi_hari_ini
    FROM public.absensi
    WHERE nim = p_nim AND tanggal = v_tanggal_hari_ini;

    IF p_action = 'checkin' THEN
        IF v_absensi_hari_ini IS NOT NULL THEN
            IF v_absensi_hari_ini.waktu_datang IS NOT NULL THEN
                p_result_code := -3;
                p_result_message := 'Sudah check in hari ini';
                p_id_absensi := v_absensi_hari_ini.id_absensi;
                RETURN;
            END IF;

            UPDATE public.absensi
            SET waktu_datang = CURRENT_TIMESTAMP,
                keterangan = COALESCE(p_keterangan, keterangan)
            WHERE id_absensi = v_absensi_hari_ini.id_absensi;

            p_id_absensi := v_absensi_hari_ini.id_absensi;
        ELSE
            INSERT INTO public.absensi (
                nim, tanggal, waktu_datang, keterangan
            )
            VALUES (
                p_nim, v_tanggal_hari_ini, CURRENT_TIMESTAMP, p_keterangan
            )
            RETURNING id_absensi INTO p_id_absensi;
        END IF;

        p_result_code := 1;
        p_result_message := 'Check in berhasil';

    ELSIF p_action = 'checkout' THEN
        IF v_absensi_hari_ini IS NULL OR v_absensi_hari_ini.waktu_datang IS NULL THEN
            p_result_code := -4;
            p_result_message := 'Belum check in hari ini';
            RETURN;
        END IF;

        IF v_absensi_hari_ini.waktu_pulang IS NOT NULL THEN
            p_result_code := -5;
            p_result_message := 'Sudah check out hari ini';
            p_id_absensi := v_absensi_hari_ini.id_absensi;
            RETURN;
        END IF;

        UPDATE public.absensi
        SET waktu_pulang = CURRENT_TIMESTAMP,
            keterangan = COALESCE(p_keterangan, keterangan)
        WHERE id_absensi = v_absensi_hari_ini.id_absensi;

        p_id_absensi := v_absensi_hari_ini.id_absensi;
        p_result_code := 1;
        p_result_message := 'Check out berhasil';
    END IF;
END;
$$;


ALTER PROCEDURE public.proc_update_absensi(IN p_nim character varying, IN p_action character varying, IN p_keterangan text, OUT p_id_absensi integer, OUT p_result_code integer, OUT p_result_message character varying) OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 243 (class 1259 OID 46534)
-- Name: absensi; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.absensi (
    id_absensi integer NOT NULL,
    id_mhs integer NOT NULL,
    waktu_datang timestamp without time zone,
    waktu_pulang timestamp without time zone,
    keterangan text,
    tanggal date DEFAULT CURRENT_DATE NOT NULL
);


ALTER TABLE public.absensi OWNER TO postgres;

--
-- TOC entry 242 (class 1259 OID 46533)
-- Name: absensi_id_absensi_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.absensi_id_absensi_seq
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


<<<<<<< HEAD
ALTER TABLE public.mitra_id_mitra_seq OWNER TO postgres;

--
-- TOC entry 3605 (class 0 OID 0)
-- Dependencies: 224
-- Name: mitra_id_mitra_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.mitra_id_mitra_seq OWNED BY public.mitra.id_mitra;


--
-- TOC entry 239 (class 1259 OID 35248)
-- Name: peminjaman; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.peminjaman (
    id_peminjaman integer NOT NULL,
    id_alat integer NOT NULL,
    nama_peminjam character varying(255) NOT NULL,
    tanggal_pinjam date NOT NULL,
    tanggal_kembali date,
    status character varying(50) DEFAULT 'dipinjam'::character varying,
    keterangan text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    id_ruang integer,
    waktu_pinjam time without time zone,
    waktu_kembali time without time zone,
    CONSTRAINT chk_waktu_logical CHECK (((tanggal_pinjam IS NULL) OR (tanggal_kembali IS NULL) OR ((waktu_pinjam IS NULL) OR (waktu_kembali IS NULL)) OR ((tanggal_pinjam < tanggal_kembali) OR ((tanggal_pinjam = tanggal_kembali) AND (waktu_kembali > waktu_pinjam)))))
);


ALTER TABLE public.peminjaman OWNER TO postgres;

--
-- TOC entry 238 (class 1259 OID 35247)
-- Name: peminjaman_id_peminjaman_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.peminjaman_id_peminjaman_seq
=======
ALTER TABLE public.absensi_id_absensi_seq OWNER TO postgres;

--
-- TOC entry 3627 (class 0 OID 0)
-- Dependencies: 242
-- Name: absensi_id_absensi_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.absensi_id_absensi_seq OWNED BY public.absensi.id_absensi;


--
-- TOC entry 227 (class 1259 OID 46457)
-- Name: admin; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.admin (
    id_admin integer NOT NULL,
    username character varying(100) NOT NULL,
    password_hash character varying(255) NOT NULL,
    role character varying(50) DEFAULT 'user'::character varying,
    created_at timestamp with time zone DEFAULT now()
);


ALTER TABLE public.admin OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 46456)
-- Name: admin_id_admin_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.admin_id_admin_seq
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


<<<<<<< HEAD
ALTER TABLE public.peminjaman_id_peminjaman_seq OWNER TO postgres;

--
-- TOC entry 3606 (class 0 OID 0)
-- Dependencies: 238
-- Name: peminjaman_id_peminjaman_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.peminjaman_id_peminjaman_seq OWNED BY public.peminjaman.id_peminjaman;


--
-- TOC entry 231 (class 1259 OID 35206)
-- Name: penelitian; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.penelitian (
    id_penelitian integer NOT NULL,
    id_artikel integer,
    nim character varying(20),
    judul character varying(255) NOT NULL,
    tahun integer,
    id_member integer,
    deskripsi text,
    created_at timestamp with time zone DEFAULT now(),
    id_produk integer,
    id_mitra integer,
    tgl_mulai date DEFAULT CURRENT_DATE NOT NULL,
    tgl_selesai date,
    id_fp integer
);


ALTER TABLE public.penelitian OWNER TO postgres;

--
-- TOC entry 230 (class 1259 OID 35205)
-- Name: penelitian_id_penelitian_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.penelitian_id_penelitian_seq
=======
ALTER TABLE public.admin_id_admin_seq OWNER TO postgres;

--
-- TOC entry 3628 (class 0 OID 0)
-- Dependencies: 226
-- Name: admin_id_admin_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.admin_id_admin_seq OWNED BY public.admin.id_admin;


--
-- TOC entry 245 (class 1259 OID 46544)
-- Name: alat_lab; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.alat_lab (
    id_alat_lab integer NOT NULL,
    nama_alat character varying(255) NOT NULL,
    deskripsi text,
    stock integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    id_admin integer
);


ALTER TABLE public.alat_lab OWNER TO postgres;

--
-- TOC entry 244 (class 1259 OID 46543)
-- Name: alat_lab_id_alat_lab_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.alat_lab_id_alat_lab_seq
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


<<<<<<< HEAD
ALTER TABLE public.penelitian_id_penelitian_seq OWNER TO postgres;

--
-- TOC entry 3607 (class 0 OID 0)
-- Dependencies: 230
-- Name: penelitian_id_penelitian_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.penelitian_id_penelitian_seq OWNED BY public.penelitian.id_penelitian;


--
-- TOC entry 245 (class 1259 OID 35289)
-- Name: pengunjung; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pengunjung (
    id_pengunjung integer NOT NULL,
    nama character varying(150),
    email character varying(150),
    asal_institusi character varying(200),
    created_at timestamp with time zone DEFAULT now(),
    no_hp character varying(20),
    pesan text
);


ALTER TABLE public.pengunjung OWNER TO postgres;

--
-- TOC entry 244 (class 1259 OID 35288)
-- Name: pengunjung_id_pengunjung_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pengunjung_id_pengunjung_seq
=======
ALTER TABLE public.alat_lab_id_alat_lab_seq OWNER TO postgres;

--
-- TOC entry 3629 (class 0 OID 0)
-- Dependencies: 244
-- Name: alat_lab_id_alat_lab_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.alat_lab_id_alat_lab_seq OWNED BY public.alat_lab.id_alat_lab;


--
-- TOC entry 237 (class 1259 OID 46505)
-- Name: artikel; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.artikel (
    id_artikel integer NOT NULL,
    judul character varying(255) NOT NULL,
    tahun integer,
    konten character varying(4000),
    id_penelitian integer,
    nim character varying(50),
    id_member integer,
    id_produk integer,
    id_mitra integer
);


ALTER TABLE public.artikel OWNER TO postgres;

--
-- TOC entry 236 (class 1259 OID 46504)
-- Name: artikel_id_artikel_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.artikel_id_artikel_seq
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


<<<<<<< HEAD
ALTER TABLE public.pengunjung_id_pengunjung_seq OWNER TO postgres;

--
-- TOC entry 3608 (class 0 OID 0)
-- Dependencies: 244
-- Name: pengunjung_id_pengunjung_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pengunjung_id_pengunjung_seq OWNED BY public.pengunjung.id_pengunjung;


--
-- TOC entry 233 (class 1259 OID 35217)
-- Name: produk; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.produk (
    id_produk integer NOT NULL,
    nama_produk character varying(255) NOT NULL,
    deskripsi text
);


ALTER TABLE public.produk OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 35216)
-- Name: produk_id_produk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.produk_id_produk_seq
=======
ALTER TABLE public.artikel_id_artikel_seq OWNER TO postgres;

--
-- TOC entry 3630 (class 0 OID 0)
-- Dependencies: 236
-- Name: artikel_id_artikel_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.artikel_id_artikel_seq OWNED BY public.artikel.id_artikel;


--
-- TOC entry 235 (class 1259 OID 46495)
-- Name: berita; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.berita (
    id_berita integer NOT NULL,
    judul character varying(255) NOT NULL,
    konten character varying(4000),
    gambar_thumbnail text,
    created_at timestamp with time zone DEFAULT now(),
    id_admin integer
);


ALTER TABLE public.berita OWNER TO postgres;

--
-- TOC entry 234 (class 1259 OID 46494)
-- Name: berita_id_berita_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.berita_id_berita_seq
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


<<<<<<< HEAD
ALTER TABLE public.produk_id_produk_seq OWNER TO postgres;

--
-- TOC entry 3609 (class 0 OID 0)
-- Dependencies: 232
-- Name: produk_id_produk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.produk_id_produk_seq OWNED BY public.produk.id_produk;


--
-- TOC entry 241 (class 1259 OID 35260)
-- Name: ruang_lab; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ruang_lab (
    id_ruang_lab integer NOT NULL,
    nama_ruang character varying(150) NOT NULL,
    status character varying(30) DEFAULT 'tersedia'::character varying NOT NULL,
    id_admin integer,
    created_at timestamp with time zone DEFAULT now() NOT NULL
);


ALTER TABLE public.ruang_lab OWNER TO postgres;

--
-- TOC entry 240 (class 1259 OID 35259)
-- Name: ruang_lab_id_ruang_lab_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ruang_lab_id_ruang_lab_seq
=======
ALTER TABLE public.berita_id_berita_seq OWNER TO postgres;

--
-- TOC entry 3631 (class 0 OID 0)
-- Dependencies: 234
-- Name: berita_id_berita_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.berita_id_berita_seq OWNED BY public.berita.id_berita;


--
-- TOC entry 225 (class 1259 OID 46443)
-- Name: buku_tamu; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.buku_tamu (
    id_buku_tamu integer NOT NULL,
    nama character varying(150) NOT NULL,
    email character varying(150) NOT NULL,
    institusi character varying(200) NOT NULL,
    no_hp character varying(50) NOT NULL,
    pesan character varying(2000),
    created_at timestamp with time zone DEFAULT now(),
    is_read boolean DEFAULT false,
    admin_response character varying(2000)
);


ALTER TABLE public.buku_tamu OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 46442)
-- Name: buku_tamu_id_buku_tamu_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.buku_tamu_id_buku_tamu_seq
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


<<<<<<< HEAD
ALTER TABLE public.ruang_lab_id_ruang_lab_seq OWNER TO postgres;

--
-- TOC entry 3610 (class 0 OID 0)
-- Dependencies: 240
-- Name: ruang_lab_id_ruang_lab_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ruang_lab_id_ruang_lab_seq OWNED BY public.ruang_lab.id_ruang_lab;


--
-- TOC entry 255 (class 1259 OID 42455)
-- Name: settings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.settings (
    id_setting integer NOT NULL,
    site_title character varying(255) NOT NULL,
    site_subtitle text,
    site_logo character varying(255),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_by character varying(255),
    id_footer integer,
    id_contact integer,
    page_titles jsonb DEFAULT '{}'::jsonb,
    footer_logo character varying(255),
    footer_title character varying(255),
    copyright_text text,
    contact_email character varying(255),
    contact_phone character varying(100),
    contact_address text
);


ALTER TABLE public.settings OWNER TO postgres;

--
-- TOC entry 248 (class 1259 OID 35410)
-- Name: view_alat_dipinjam; Type: VIEW; Schema: public; Owner: postgres
--

=======
ALTER TABLE public.buku_tamu_id_buku_tamu_seq OWNER TO postgres;

--
-- TOC entry 3632 (class 0 OID 0)
-- Dependencies: 224
-- Name: buku_tamu_id_buku_tamu_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.buku_tamu_id_buku_tamu_seq OWNED BY public.buku_tamu.id_buku_tamu;


--
-- TOC entry 261 (class 1259 OID 46822)
-- Name: contact_info; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.contact_info (
    id_contact integer NOT NULL,
    contact_email character varying(255),
    contact_phone character varying(100),
    contact_address character varying(255)
);


ALTER TABLE public.contact_info OWNER TO postgres;

--
-- TOC entry 262 (class 1259 OID 46827)
-- Name: contact_info_id_contact_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.contact_info_id_contact_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.contact_info_id_contact_seq OWNER TO postgres;

--
-- TOC entry 3633 (class 0 OID 0)
-- Dependencies: 262
-- Name: contact_info_id_contact_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.contact_info_id_contact_seq OWNED BY public.contact_info.id_contact;


--
-- TOC entry 251 (class 1259 OID 46577)
-- Name: fokus_penelitian; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fokus_penelitian (
    id_fp integer NOT NULL,
    title character varying(200) NOT NULL,
    deskripsi text,
    detail text NOT NULL
);


ALTER TABLE public.fokus_penelitian OWNER TO postgres;

--
-- TOC entry 250 (class 1259 OID 46576)
-- Name: fokus_penelitian_id_fp_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fokus_penelitian_id_fp_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fokus_penelitian_id_fp_seq OWNER TO postgres;

--
-- TOC entry 3634 (class 0 OID 0)
-- Dependencies: 250
-- Name: fokus_penelitian_id_fp_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fokus_penelitian_id_fp_seq OWNED BY public.fokus_penelitian.id_fp;


--
-- TOC entry 263 (class 1259 OID 46828)
-- Name: footer_settings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.footer_settings (
    id_footer integer NOT NULL,
    footer_logo character varying(255),
    footer_title character varying(255),
    footer_subtitle character varying(255),
    copyright_text text
);


ALTER TABLE public.footer_settings OWNER TO postgres;

--
-- TOC entry 264 (class 1259 OID 46833)
-- Name: footer_settings_id_footer_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.footer_settings_id_footer_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.footer_settings_id_footer_seq OWNER TO postgres;

--
-- TOC entry 3635 (class 0 OID 0)
-- Dependencies: 264
-- Name: footer_settings_id_footer_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.footer_settings_id_footer_seq OWNED BY public.footer_settings.id_footer;


--
-- TOC entry 223 (class 1259 OID 46430)
-- Name: gallery; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.gallery (
    id_gallery integer NOT NULL,
    id_berita integer,
    gambar text,
    judul character varying(255),
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now()
);


ALTER TABLE public.gallery OWNER TO postgres;

--
-- TOC entry 222 (class 1259 OID 46429)
-- Name: gallery_id_gallery_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.gallery_id_gallery_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.gallery_id_gallery_seq OWNER TO postgres;

--
-- TOC entry 3636 (class 0 OID 0)
-- Dependencies: 222
-- Name: gallery_id_gallery_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.gallery_id_gallery_seq OWNED BY public.gallery.id_gallery;


--
-- TOC entry 229 (class 1259 OID 46468)
-- Name: mahasiswa; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mahasiswa (
    id_mahasiswa integer NOT NULL,
    nama character varying(150) NOT NULL,
    tahun integer,
    status character varying(20) DEFAULT 'regular'::character varying NOT NULL,
    id_admin integer,
    CONSTRAINT chk_mahasiswa_status CHECK (((status)::text = ANY ((ARRAY['magang'::character varying, 'skripsi'::character varying, 'regular'::character varying])::text[])))
);


ALTER TABLE public.mahasiswa OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 46467)
-- Name: mahasiswa_id_mahasiswa_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.mahasiswa_id_mahasiswa_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mahasiswa_id_mahasiswa_seq OWNER TO postgres;

--
-- TOC entry 3637 (class 0 OID 0)
-- Dependencies: 228
-- Name: mahasiswa_id_mahasiswa_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.mahasiswa_id_mahasiswa_seq OWNED BY public.mahasiswa.id_mahasiswa;


--
-- TOC entry 265 (class 1259 OID 46834)
-- Name: mahasiswa_nim_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.mahasiswa_nim_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mahasiswa_nim_seq OWNER TO postgres;

--
-- TOC entry 231 (class 1259 OID 46477)
-- Name: member; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.member (
    id_member integer NOT NULL,
    nama character varying(150) NOT NULL,
    email character varying(150),
    jabatan character varying(100),
    foto character varying(255),
    bidang_keahlian character varying(255),
    notlp character varying(30),
    deskripsi text,
    alamat text,
    id_admin integer,
    google_scholar text
);


ALTER TABLE public.member OWNER TO postgres;

--
-- TOC entry 230 (class 1259 OID 46476)
-- Name: member_id_member_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.member_id_member_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.member_id_member_seq OWNER TO postgres;

--
-- TOC entry 3638 (class 0 OID 0)
-- Dependencies: 230
-- Name: member_id_member_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.member_id_member_seq OWNED BY public.member.id_member;


--
-- TOC entry 233 (class 1259 OID 46486)
-- Name: mitra; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mitra (
    id_mitra integer NOT NULL,
    nama_institusi character varying(255) NOT NULL,
    logo character varying(255)
);


ALTER TABLE public.mitra OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 46485)
-- Name: mitra_id_mitra_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.mitra_id_mitra_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mitra_id_mitra_seq OWNER TO postgres;

--
-- TOC entry 3639 (class 0 OID 0)
-- Dependencies: 232
-- Name: mitra_id_mitra_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.mitra_id_mitra_seq OWNED BY public.mitra.id_mitra;


--
-- TOC entry 247 (class 1259 OID 46556)
-- Name: peminjaman; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.peminjaman (
    id_peminjaman integer NOT NULL,
    id_alat integer NOT NULL,
    nama_peminjam character varying(255) NOT NULL,
    tanggal_pinjam date NOT NULL,
    tanggal_kembali date,
    status character varying(50) DEFAULT 'dipinjam'::character varying,
    keterangan text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    id_ruang integer,
    waktu_pinjam time without time zone,
    waktu_kembali time without time zone,
    CONSTRAINT chk_waktu_logical CHECK (((tanggal_pinjam IS NULL) OR (tanggal_kembali IS NULL) OR ((waktu_pinjam IS NULL) OR (waktu_kembali IS NULL)) OR ((tanggal_pinjam < tanggal_kembali) OR ((tanggal_pinjam = tanggal_kembali) AND (waktu_kembali > waktu_pinjam)))))
);


ALTER TABLE public.peminjaman OWNER TO postgres;

--
-- TOC entry 246 (class 1259 OID 46555)
-- Name: peminjaman_id_peminjaman_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.peminjaman_id_peminjaman_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.peminjaman_id_peminjaman_seq OWNER TO postgres;

--
-- TOC entry 3640 (class 0 OID 0)
-- Dependencies: 246
-- Name: peminjaman_id_peminjaman_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.peminjaman_id_peminjaman_seq OWNED BY public.peminjaman.id_peminjaman;


--
-- TOC entry 239 (class 1259 OID 46514)
-- Name: penelitian; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.penelitian (
    id_penelitian integer NOT NULL,
    id_artikel integer,
    id_mhs integer,
    judul character varying(255) NOT NULL,
    tahun integer,
    id_member integer,
    deskripsi text,
    created_at timestamp with time zone DEFAULT now(),
    id_produk integer,
    id_mitra integer,
    tgl_mulai date DEFAULT CURRENT_DATE NOT NULL,
    tgl_selesai date,
    id_fp integer
);


ALTER TABLE public.penelitian OWNER TO postgres;

--
-- TOC entry 238 (class 1259 OID 46513)
-- Name: penelitian_id_penelitian_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.penelitian_id_penelitian_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.penelitian_id_penelitian_seq OWNER TO postgres;

--
-- TOC entry 3641 (class 0 OID 0)
-- Dependencies: 238
-- Name: penelitian_id_penelitian_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.penelitian_id_penelitian_seq OWNED BY public.penelitian.id_penelitian;


--
-- TOC entry 255 (class 1259 OID 46597)
-- Name: pengunjung; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pengunjung (
    id_pengunjung integer NOT NULL,
    nama character varying(150),
    email character varying(150),
    asal_institusi character varying(200),
    created_at timestamp with time zone DEFAULT now(),
    no_hp character varying(20),
    pesan text
);


ALTER TABLE public.pengunjung OWNER TO postgres;

--
-- TOC entry 254 (class 1259 OID 46596)
-- Name: pengunjung_id_pengunjung_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pengunjung_id_pengunjung_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pengunjung_id_pengunjung_seq OWNER TO postgres;

--
-- TOC entry 3642 (class 0 OID 0)
-- Dependencies: 254
-- Name: pengunjung_id_pengunjung_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pengunjung_id_pengunjung_seq OWNED BY public.pengunjung.id_pengunjung;


--
-- TOC entry 241 (class 1259 OID 46525)
-- Name: produk; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.produk (
    id_produk integer NOT NULL,
    nama_produk character varying(255) NOT NULL,
    deskripsi text,
    gambar character varying(255),
    try text
);


ALTER TABLE public.produk OWNER TO postgres;

--
-- TOC entry 240 (class 1259 OID 46524)
-- Name: produk_id_produk_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.produk_id_produk_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.produk_id_produk_seq OWNER TO postgres;

--
-- TOC entry 3643 (class 0 OID 0)
-- Dependencies: 240
-- Name: produk_id_produk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.produk_id_produk_seq OWNED BY public.produk.id_produk;


--
-- TOC entry 249 (class 1259 OID 46568)
-- Name: ruang_lab; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ruang_lab (
    id_ruang_lab integer NOT NULL,
    nama_ruang character varying(150) NOT NULL,
    status character varying(30) DEFAULT 'tersedia'::character varying NOT NULL,
    id_admin integer,
    created_at timestamp with time zone DEFAULT now() NOT NULL
);


ALTER TABLE public.ruang_lab OWNER TO postgres;

--
-- TOC entry 248 (class 1259 OID 46567)
-- Name: ruang_lab_id_ruang_lab_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ruang_lab_id_ruang_lab_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ruang_lab_id_ruang_lab_seq OWNER TO postgres;

--
-- TOC entry 3644 (class 0 OID 0)
-- Dependencies: 248
-- Name: ruang_lab_id_ruang_lab_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ruang_lab_id_ruang_lab_seq OWNED BY public.ruang_lab.id_ruang_lab;


--
-- TOC entry 253 (class 1259 OID 46586)
-- Name: settings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.settings (
    id_setting integer NOT NULL,
    site_title character varying(255) NOT NULL,
    site_subtitle character varying(255),
    site_logo character varying(255),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    footer_logo character varying(255),
    footer_title character varying(255),
    copyright_text text,
    contact_email character varying(255),
    contact_phone character varying(100),
    contact_address text,
    updated_by integer,
    page_titles jsonb DEFAULT '{}'::jsonb
);


ALTER TABLE public.settings OWNER TO postgres;

--
-- TOC entry 252 (class 1259 OID 46585)
-- Name: settings_id_setting_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.settings_id_setting_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.settings_id_setting_seq OWNER TO postgres;

--
-- TOC entry 3645 (class 0 OID 0)
-- Dependencies: 252
-- Name: settings_id_setting_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.settings_id_setting_seq OWNED BY public.settings.id_setting;


--
-- TOC entry 267 (class 1259 OID 47155)
-- Name: video; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.video (
    id_video integer NOT NULL,
    title character varying(255) NOT NULL,
    href_link text,
    created_at timestamp with time zone DEFAULT now()
);


ALTER TABLE public.video OWNER TO postgres;

--
-- TOC entry 266 (class 1259 OID 47154)
-- Name: video_id_video_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.video_id_video_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.video_id_video_seq OWNER TO postgres;

--
-- TOC entry 3646 (class 0 OID 0)
-- Dependencies: 266
-- Name: video_id_video_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.video_id_video_seq OWNED BY public.video.id_video;


--
-- TOC entry 258 (class 1259 OID 46803)
-- Name: view_alat_dipinjam; Type: VIEW; Schema: public; Owner: postgres
--

>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
CREATE VIEW public.view_alat_dipinjam AS
 SELECT pj.id_peminjaman,
    pj.id_alat,
    alat.nama_alat,
    alat.deskripsi,
    pj.nama_peminjam,
    pj.tanggal_pinjam,
    pj.tanggal_kembali,
    pj.keterangan,
    pj.status,
    pj.created_at,
    pj.id_ruang
   FROM (public.peminjaman pj
     LEFT JOIN public.alat_lab alat ON ((alat.id_alat_lab = pj.id_alat)))
  WHERE ((pj.status)::text = 'dipinjam'::text);


ALTER TABLE public.view_alat_dipinjam OWNER TO postgres;

--
-- TOC entry 259 (class 1259 OID 46808)
-- Name: view_alat_tersedia; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.view_alat_tersedia AS
 SELECT alat.id_alat_lab,
    alat.nama_alat,
    alat.deskripsi,
    alat.stock,
    COALESCE(pj.jumlah_dipinjam, (0)::bigint) AS jumlah_dipinjam,
    (alat.stock - COALESCE(pj.jumlah_dipinjam, (0)::bigint)) AS stok_tersedia
   FROM (public.alat_lab alat
     LEFT JOIN ( SELECT peminjaman.id_alat,
            count(*) AS jumlah_dipinjam
           FROM public.peminjaman
          WHERE ((peminjaman.status)::text = 'dipinjam'::text)
          GROUP BY peminjaman.id_alat) pj ON ((pj.id_alat = alat.id_alat_lab)));


ALTER TABLE public.view_alat_tersedia OWNER TO postgres;

--
-- TOC entry 260 (class 1259 OID 46813)
-- Name: view_ruang_dipinjam; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.view_ruang_dipinjam AS
 SELECT pj.id_peminjaman,
    pj.id_ruang,
    r.nama_ruang,
    r.status AS status_ruang,
    pj.nama_peminjam,
    pj.tanggal_pinjam,
    pj.tanggal_kembali,
    pj.waktu_pinjam,
    pj.waktu_kembali,
    pj.keterangan,
    pj.status,
    pj.created_at
   FROM (public.peminjaman pj
     JOIN public.ruang_lab r ON ((r.id_ruang_lab = pj.id_ruang)))
  WHERE ((pj.status)::text = 'dipinjam'::text);


ALTER TABLE public.view_ruang_dipinjam OWNER TO postgres;

--
-- TOC entry 257 (class 1259 OID 46607)
-- Name: visitor; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.visitor (
    id_visitor integer NOT NULL,
    id_pengunjung integer NOT NULL,
    visit_count integer DEFAULT 0 NOT NULL,
    last_visit timestamp with time zone,
    first_visit timestamp with time zone DEFAULT now(),
    keterangan character varying(500),
    is_read boolean DEFAULT false,
    admin_response text
);


ALTER TABLE public.visitor OWNER TO postgres;

--
-- TOC entry 256 (class 1259 OID 46606)
-- Name: visitor_id_visitor_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.visitor_id_visitor_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.visitor_id_visitor_seq OWNER TO postgres;

--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3611 (class 0 OID 0)
=======
-- TOC entry 3615 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 246
=======
-- TOC entry 3647 (class 0 OID 0)
-- Dependencies: 256
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: visitor_id_visitor_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.visitor_id_visitor_seq OWNED BY public.visitor.id_visitor;


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3301 (class 2604 OID 35229)
=======
-- TOC entry 3305 (class 2604 OID 35229)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3320 (class 2604 OID 47310)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: absensi id_absensi; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.absensi ALTER COLUMN id_absensi SET DEFAULT nextval('public.absensi_id_absensi_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3287 (class 2604 OID 35152)
=======
-- TOC entry 3291 (class 2604 OID 35152)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3306 (class 2604 OID 47313)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: admin id_admin; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin ALTER COLUMN id_admin SET DEFAULT nextval('public.admin_id_admin_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3303 (class 2604 OID 35239)
=======
-- TOC entry 3307 (class 2604 OID 35239)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3322 (class 2604 OID 47315)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: alat_lab id_alat_lab; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.alat_lab ALTER COLUMN id_alat_lab SET DEFAULT nextval('public.alat_lab_id_alat_lab_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3296 (class 2604 OID 35200)
=======
-- TOC entry 3300 (class 2604 OID 35200)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3315 (class 2604 OID 47317)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: artikel id_artikel; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.artikel ALTER COLUMN id_artikel SET DEFAULT nextval('public.artikel_id_artikel_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3294 (class 2604 OID 35190)
=======
-- TOC entry 3298 (class 2604 OID 35190)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3313 (class 2604 OID 47319)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: berita id_berita; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.berita ALTER COLUMN id_berita SET DEFAULT nextval('public.berita_id_berita_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3284 (class 2604 OID 35138)
=======
-- TOC entry 3288 (class 2604 OID 35138)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3303 (class 2604 OID 47321)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: buku_tamu id_buku_tamu; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.buku_tamu ALTER COLUMN id_buku_tamu SET DEFAULT nextval('public.buku_tamu_id_buku_tamu_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3321 (class 2604 OID 42431)
=======
-- TOC entry 3325 (class 2604 OID 42431)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3343 (class 2604 OID 47323)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: contact_info id_contact; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contact_info ALTER COLUMN id_contact SET DEFAULT nextval('public.contact_info_id_contact_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3313 (class 2604 OID 35272)
=======
-- TOC entry 3317 (class 2604 OID 35272)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3332 (class 2604 OID 47325)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: fokus_penelitian id_fp; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fokus_penelitian ALTER COLUMN id_fp SET DEFAULT nextval('public.fokus_penelitian_id_fp_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3320 (class 2604 OID 42422)
=======
-- TOC entry 3324 (class 2604 OID 42422)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3344 (class 2604 OID 47327)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: footer_settings id_footer; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.footer_settings ALTER COLUMN id_footer SET DEFAULT nextval('public.footer_settings_id_footer_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3281 (class 2604 OID 35125)
=======
-- TOC entry 3285 (class 2604 OID 35125)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3300 (class 2604 OID 47329)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: gallery id_gallery; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gallery ALTER COLUMN id_gallery SET DEFAULT nextval('public.gallery_id_gallery_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3290 (class 2604 OID 35536)
-- Name: mahasiswa nim; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mahasiswa ALTER COLUMN nim SET DEFAULT nextval('public.mahasiswa_id_mahasiswa_seq'::regclass);


--
-- TOC entry 3292 (class 2604 OID 35172)
=======
-- TOC entry 3294 (class 2604 OID 35536)
-- Name: mahasiswa nim; Type: DEFAULT; Schema: public; Owner: postgres
=======
-- TOC entry 3309 (class 2604 OID 47331)
-- Name: mahasiswa id_mahasiswa; Type: DEFAULT; Schema: public; Owner: postgres
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
--

ALTER TABLE ONLY public.mahasiswa ALTER COLUMN id_mahasiswa SET DEFAULT nextval('public.mahasiswa_id_mahasiswa_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
-- TOC entry 3296 (class 2604 OID 35172)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3311 (class 2604 OID 47333)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: member id_member; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.member ALTER COLUMN id_member SET DEFAULT nextval('public.member_id_member_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3293 (class 2604 OID 35181)
=======
-- TOC entry 3297 (class 2604 OID 35181)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3312 (class 2604 OID 47335)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: mitra id_mitra; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mitra ALTER COLUMN id_mitra SET DEFAULT nextval('public.mitra_id_mitra_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3307 (class 2604 OID 35251)
=======
-- TOC entry 3311 (class 2604 OID 35251)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3326 (class 2604 OID 47337)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: peminjaman id_peminjaman; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.peminjaman ALTER COLUMN id_peminjaman SET DEFAULT nextval('public.peminjaman_id_peminjaman_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3297 (class 2604 OID 35209)
=======
-- TOC entry 3301 (class 2604 OID 35209)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3316 (class 2604 OID 47339)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: penelitian id_penelitian; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian ALTER COLUMN id_penelitian SET DEFAULT nextval('public.penelitian_id_penelitian_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3314 (class 2604 OID 35292)
=======
-- TOC entry 3318 (class 2604 OID 35292)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3337 (class 2604 OID 47340)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: pengunjung id_pengunjung; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pengunjung ALTER COLUMN id_pengunjung SET DEFAULT nextval('public.pengunjung_id_pengunjung_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3300 (class 2604 OID 35220)
=======
-- TOC entry 3304 (class 2604 OID 35220)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3319 (class 2604 OID 47341)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: produk id_produk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.produk ALTER COLUMN id_produk SET DEFAULT nextval('public.produk_id_produk_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3310 (class 2604 OID 35263)
=======
-- TOC entry 3314 (class 2604 OID 35263)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3329 (class 2604 OID 47343)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: ruang_lab id_ruang_lab; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ruang_lab ALTER COLUMN id_ruang_lab SET DEFAULT nextval('public.ruang_lab_id_ruang_lab_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3316 (class 2604 OID 35302)
=======
-- TOC entry 3320 (class 2604 OID 35302)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3333 (class 2604 OID 47346)
-- Name: settings id_setting; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings ALTER COLUMN id_setting SET DEFAULT nextval('public.settings_id_setting_seq'::regclass);


--
-- TOC entry 3345 (class 2604 OID 47349)
-- Name: video id_video; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.video ALTER COLUMN id_video SET DEFAULT nextval('public.video_id_video_seq'::regclass);


--
-- TOC entry 3339 (class 2604 OID 47348)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: visitor id_visitor; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visitor ALTER COLUMN id_visitor SET DEFAULT nextval('public.visitor_id_visitor_seq'::regclass);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3570 (class 0 OID 35226)
=======
-- TOC entry 3574 (class 0 OID 35226)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 235
=======
-- TOC entry 3599 (class 0 OID 46534)
-- Dependencies: 243
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: absensi; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.absensi (id_absensi, id_mhs, waktu_datang, waktu_pulang, keterangan, tanggal) FROM stdin;
1	1901234570	2025-12-07 22:37:28.717214	\N	Status: Magang | hadir	2025-12-07
2	1901234570	2025-12-08 07:46:36.713207	2025-12-08 07:46:38.563688	Status: Magang | saya pulang	2025-12-08
3	110202398	2025-12-09 15:37:08.927232	\N	Status: Skripsi	2025-12-09
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3554 (class 0 OID 35149)
=======
-- TOC entry 3558 (class 0 OID 35149)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 219
=======
-- TOC entry 3583 (class 0 OID 46457)
-- Dependencies: 227
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: admin; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.admin (id_admin, username, password_hash, role, created_at) FROM stdin;
5	falah	$2y$10$lxfQYGWQtHq05skVYB9fjOAXYabRhkTNf.s5EdxhqEYWD6d2WRV62	admin	2025-12-04 08:52:59.472229+07
4	admin	$2y$10$zYpf47dAlg0u4ABEqKpCGur7EIgbtGFNX5EPLNwDaqAZGVqH2aiB6	admin	2025-12-02 13:11:36.651666+07
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3572 (class 0 OID 35236)
=======
-- TOC entry 3576 (class 0 OID 35236)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 237
=======
-- TOC entry 3601 (class 0 OID 46544)
-- Dependencies: 245
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: alat_lab; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.alat_lab (id_alat_lab, nama_alat, deskripsi, stock, created_at, updated_at, id_admin) FROM stdin;
9	obeng	\N	3	2025-12-04 10:50:40.806202	2025-12-04 10:50:40.806202	\N
0	Room Placeholder	Dummy alat for room borrowing	0	2025-12-08 05:11:31.240607	2025-12-08 05:11:31.240607	\N
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3564 (class 0 OID 35197)
=======
-- TOC entry 3568 (class 0 OID 35197)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 229
=======
-- TOC entry 3593 (class 0 OID 46505)
-- Dependencies: 237
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: artikel; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.artikel (id_artikel, judul, tahun, konten, id_penelitian, nim, id_member, id_produk, id_mitra) FROM stdin;
14	Enhancing EFL Reading Comprehension via an AI-Chatbot-Guided Toulmin Mapping in Viat-Map	2025	https://journal.unilak.ac.id/index.php/UTAMAX/article/download/26628/8190	3	\N	5	1	\N
13	Analyzing Student Behavior in Viat-map: Steps and Time as Performance Indicators	2024	https://scholar.google.com/citations?view_op=view_citation&hl=en&user=jetyPtUAAAAJ&sortby=pubdate&citation_for_view=jetyPtUAAAAJ:R3hNpaxXUhUC	3	\N	5	\N	\N
12	Gamification in Viat-Map Application to Improve Student’s Experiences and the Relation with Their Performance	2024	https://scholar.google.com/citations?view_op=view_citation&hl=en&user=jetyPtUAAAAJ&sortby=pubdate&citation_for_view=jetyPtUAAAAJ:TQgYirikUcIC	3	\N	5	1	\N
9	\tInvestigating VIAT-Map from the view point of Ease of Use, Perceived of Usefulness, and Acceptance of IT by using Technology Acceptance Model (TAM)	2023	https://ieeexplore.ieee.org/abstract/document/10435078/	3	\N	5	1	\N
10	Experimental Comparison of Promotion Effect for EFL Reading Comprehension between Conventional Summarization and Toulmin Argument Reconstruction	2023	https://eds.let.media.kyoto-u.ac.jp/ICCE2023/wp-content/uploads/2023/12/ICCE2023-Proceedings-V1-1214-final.pdf	3	\N	5	1	\N
11	Improving Memory Retention By Using Source Connection Function In Viat-Map For English Reading Comprehension	2023	https://scholar.google.com/citations?view_op=view_citation&hl=en&user=jetyPtUAAAAJ&sortby=pubdate&citation_for_view=jetyPtUAAAAJ:7PzlFSSx8tAC	3	\N	5	1	\N
8	Constructing Toulmin's Logical Structure Through Viat-map Application for Reading Comprehension of EFL Students	2022	https://ieeexplore.ieee.org/abstract/document/9930471/	3	\N	5	1	\N
7	Preliminary Analysis of Result and Log Data on Viat-map in English Reading Comprehension	2022	https://ieeexplore.ieee.org/abstract/document/9967903/	3	\N	5	1	\N
6	A Preliminary Study: Toulmin Arguments in English Reading Comprehension for English as Foreign Language Students	2021	https://ieeexplore.ieee.org/abstract/document/9587398/	3	\N	5	1	\N
4	Rancang Bangun Aplikasi Strategi Grafis (Viat-Map) Untuk Reading Comprehension Dengan Toulmin Arguments	2021	https://ieeexplore.ieee.org/abstract/document/9930471/	3	\N	5	1	\N
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3562 (class 0 OID 35187)
=======
-- TOC entry 3566 (class 0 OID 35187)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 227
=======
-- TOC entry 3591 (class 0 OID 46495)
-- Dependencies: 235
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: berita; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.berita (id_berita, judul, konten, gambar_thumbnail, created_at, id_admin) FROM stdin;
3	ICCE 2024 - Atteneo University, Phillipines	3 of our member went to Phillipines to present our research. It's been a valuable experiences to meet other researcher's outside Indonesia	https://let.polinema.ac.id/assets/images/whatsapp-image-2025-04-23-at-16.31.55-58c3b98e.jpg	2025-12-08 06:33:04.448303+07	\N
4	ICAST 2024 - Bandung, Indonesia	Thrilled and deeply honored to receive the Best Paper Award at ICAST 2024! A special thank you to Professor Hee-Deung Park from Korea University and Dr. Ong Tze Ching from Kuching Polytechnic, Malaysia, for their recognition of my work. This achievement reflects the dedication and passion for advancing research in our field. Grateful for this moment and excited for the journey ahead	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-10-24-at-11.31.49-9f933f81.jpg	2025-12-08 06:33:24.833268+07	\N
5	ECTEL 2024 - Krems, Austria	Introducing VIAT-map to other researcher in ECTEL conference	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-09-23-at-15.24.49-3ff47215.jpg	2025-12-08 06:33:36.135204+07	\N
6	POLINEMA - Research EXPO 2024	Introducing VIAT-map to other Indonesian researcher\r\n\r\n	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-05-20-at-11.18.30-2ea86646.jpg	2025-12-08 06:33:49.942621+07	\N
7	Best Overall Paper Award	Enriching the research area by having Best overall paper award in ICCE 2023, Shimane Japan\r\n\r\n	https://let.polinema.ac.id/assets/images/img-7441.jpg	2025-12-08 06:34:01.801883+07	\N
8	Poster Presentation, Japan	We did a poster presentation in ICCE 2023, Matsue Japan\r\n\r\n	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-01-11-at-15.49.37-3d839794.jpeg	2025-12-08 06:34:14.629397+07	\N
9	ICCE 2023, Full Paper Presentation	We did a full paper presentation in ICCE 2023, Matsue Japan\r\n\r\n	https://let.polinema.ac.id/assets/images/whatsapp-image-2023-12-06-at-15.38.58-0c317b8b.jpg	2025-12-08 06:34:29.779428+07	\N
10	International Research Discussion Program	Enriching the research area by having Research discussion\r\n\r\n	https://let.polinema.ac.id/assets/images/whatsapp-image-2023-11-30-at-08.59.10-b558e6a2.jpg	2025-12-08 06:34:39.101737+07	\N
11	Monthly Research Discussion	Conducting a routine monthly research discussion to find new concept and finding\r\n\r\n	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-01-08-at-10.49.24-71aa8530.jpg	2025-12-08 06:34:50.846909+07	\N
12	Visiting Scientist Program	In November, 2023. we had a chance to had a research collaboration with Hiroshima University\r\n\r\n	https://let.polinema.ac.id/assets/images/whatsapp-image-2023-11-28-at-13.53.53-ad815996.jpg	2025-12-08 06:35:02.514034+07	\N
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3552 (class 0 OID 35135)
=======
-- TOC entry 3556 (class 0 OID 35135)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 217
=======
-- TOC entry 3581 (class 0 OID 46443)
-- Dependencies: 225
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: buku_tamu; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.buku_tamu (id_buku_tamu, nama, email, institusi, no_hp, pesan, created_at, is_read, admin_response) FROM stdin;
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
3	Dicky	dicky.darmawan41@sma.belajar.id	UB	13674676	wjegfyhqjewf	2025-12-02 15:39:23.575032+07	f	\N
=======
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
3	Dicky	dicky.darmawan41@sma.belajar.id	UB	13674676	wjegfyhqjewf	2025-12-02 15:39:23.575032+07	t	\N
4	Fata Haidar Aly	fata@gmail.com	ITTS	08888888888	haloo	2025-12-07 22:59:53.270576+07	t	\N
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3586 (class 0 OID 42428)
=======
-- TOC entry 3590 (class 0 OID 42428)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 254
=======
-- TOC entry 3614 (class 0 OID 46822)
-- Dependencies: 261
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: contact_info; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.contact_info (id_contact, contact_email, contact_phone, contact_address) FROM stdin;
1	info@inlet.edu	+62 823 328 645	Malang, East Java
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3578 (class 0 OID 35269)
=======
-- TOC entry 3582 (class 0 OID 35269)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 243
=======
-- TOC entry 3607 (class 0 OID 46577)
-- Dependencies: 251
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: fokus_penelitian; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.fokus_penelitian (id_fp, title, deskripsi, detail) FROM stdin;
1	Information Engineering	Pilar ini berfokus pada rekayasa sistem informasi dan pengambilan keputusan berbasis data.\r\nSubdomain seperti E-Government, Decision Support Systems, dan Civic Technology dipilih karena relevan dengan kebutuhan industri dan pemerintahan dalam membangun sistem digital yang transparan, efisien, dan etis.\r\n\r\nPilar ini mendukung pengembangan solusi teknologi untuk tata kelola publik, manajemen pengetahuan, dan sistem informasi yang patuh terhadap regulasi.	- E-Government (E-Gov)\r\n- Decision Support Systems (DSS)\r\n- Civic Technology & Digital Governance\r\n- Information Systems Architecture & Interoperability\r\n- Knowledge Management Systems\r\n- Compliance & Ethical Information Systems
3	Learning Engineering	Pilar ini menekankan rekayasa proses pembelajaran berbasis data dan desain system pembelajaran yang adaptif. Subdomain seperti Learning Analytics, AI in Education / Intelligence Tutoring System (AIED/ITS), Multimodal Learning Analytics, dan Human-Centered Learning Design dipilih untuk menjawab tantangan pendidikan modern yang menuntut personalisasi, efektivitas, dan keterukuran.\r\n\r\nPilar ini mendukung pengembangan sistem pembelajaran yang berbasis bukti, responsif terhadap kebutuhan individu, dan terintegrasi dengan teknologi canggih.	- Learning Engineering (inti)\r\n- Data / Learning Analytics\r\n- AIED / ITS (Artificial Intelligence in Education / Intelligent Tutoring Systems)\r\n- Multimodal Learning Analytics (EEG, eye-tracking, facial expression analysis)\r\n- Adaptive Learning Systems\r\n- Competency-Based Learning Design\r\n- Human-Centered Learning Design
4	Information Technology	Pilar ini mencakup teknologi inti yang menopang sistem informasi dan pembelajaran, seperti Artificial Intelligence, Cybersecurity, Cloud & Edge Computing, dan Human-Computer Interaction (HCI). Subdomain ini dipilih karena merupakan fondasi teknis yang diperlukan untuk membangun sistem yang scalable, aman, dan interoperable.\r\n\r\nPilar ini memperkuat kemampuan teknis lab dalam membangun infrastruktur digital yang mendukung riset dan implementasi teknologi.	- Technology Enhanced Learning (TEL)\r\n- Educational Gamification & Game-Based Learning\r\n- Technology Enhanced Language Learning (TELL)\r\n- Computer-Supported Collaboration (CSCL)\r\n- Virtual Reality (VR) & Augmented Reality (AR) for Learning\r\n- Learning Management Systems (LMS) &  Next-Gen Platforms & Open Educational Resources (OER) & MOOCs\r\n- Internet of Things (IoT) for Smart Learning Environments\r\n- Auto Grading Programming
5	Learning Technology	Pilar ini berfokus pada teknologi yang langsung mendukung proses pembelajaran, seperti Technology Enhanced Learning, Gamification, TELL (Technology Enhanced Language Learning), CSCL (Computer-Supported Collaborative Learning), dan VR/AR for Learning. Subdomain ini dipilih karena berkontribusi langsung terhadap peningkatan pengalaman belajar mahasiswa dan efektivitas pengajaran.\r\n\r\nPilar ini mendukung inovasi pedagogis dan pengembangan ekosistem pembelajaran digital yang menarik, inklusif, dan berbasis teknologi.	- Artificial Intelligence & Machine Learning\r\n- Big Data Infrastructure for Education\r\n- Human-Computer Interaction (HCI)
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3584 (class 0 OID 42419)
=======
-- TOC entry 3588 (class 0 OID 42419)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 252
=======
-- TOC entry 3616 (class 0 OID 46828)
-- Dependencies: 263
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: footer_settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.footer_settings (id_footer, footer_logo, footer_title, footer_subtitle, copyright_text) FROM stdin;
1	uploads/settings/img_692e5d9b52f897.08161504.png	Information and Learning Engineering	\N	© 2025 InLET - Information and Learning Engineering Technology
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3550 (class 0 OID 35122)
=======
-- TOC entry 3554 (class 0 OID 35122)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 215
=======
-- TOC entry 3579 (class 0 OID 46430)
-- Dependencies: 223
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: gallery; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.gallery (id_gallery, id_berita, gambar, judul, created_at, updated_at) FROM stdin;
6	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2025-04-23-at-16.31.54-3407f203.jpg	Gallery 1	2025-12-07 23:23:25.18913+07	2025-12-07 23:30:01.518306+07
7	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2025-04-23-at-16.31.48-d2cd465b.jpg	Gallery2	2025-12-07 23:23:36.942085+07	2025-12-07 23:30:09.473041+07
8	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2025-04-23-at-16.31.47-7e7d8ddb.jpg	Gallery3	2025-12-07 23:23:45.234107+07	2025-12-07 23:30:17.953091+07
9	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2025-04-23-at-16.31.55-58c3b98e.jpg	Gallery4	2025-12-07 23:24:00.781555+07	2025-12-07 23:30:27.329476+07
10	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-09-23-at-16.41.55-07f2a03b.jpg	Gallery5	2025-12-07 23:24:11.383051+07	2025-12-07 23:30:42.482725+07
11	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-09-23-at-16.41.55-f1d4d7ba.jpg	Gallery6	2025-12-07 23:24:35.105212+07	2025-12-07 23:30:53.493337+07
12	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-09-23-at-16.39.27-52fae16e.jpg	Gallery7	2025-12-07 23:24:48.415272+07	2025-12-07 23:31:03.460762+07
13	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-09-23-at-16.38.31-8b624303.jpg	Gallery8	2025-12-07 23:24:58.870897+07	2025-12-07 23:31:10.072469+07
14	\N	https://let.polinema.ac.id/assets/images/20240919-190114.jpg	Gallery9	2025-12-07 23:25:21.115653+07	2025-12-07 23:31:17.360678+07
15	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2023-11-28-at-13.53.55-18f0c0eb.jpg	Gallery10	2025-12-07 23:25:34.103824+07	2025-12-07 23:31:23.375231+07
16	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2023-12-06-at-15.39.02-d074d025.jpeg	Gallery11	2025-12-07 23:25:46.312463+07	2025-12-07 23:31:31.939629+07
17	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2023-12-06-at-15.38.58-0c317b8b.jpg	Gallery12	2025-12-07 23:25:57.071021+07	2025-12-07 23:31:38.896315+07
18	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2023-11-30-at-08.59.16-a0efecc9.jpg	Gallery13	2025-12-07 23:26:16.753113+07	2025-12-07 23:31:45.893659+07
19	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2023-11-28-at-13.53.54-dc456ffe.jpg	Gallery14	2025-12-07 23:26:30.527594+07	2025-12-07 23:31:57.45084+07
20	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2023-11-28-at-13.53.53-ad815996.jpg	Gallery15	2025-12-07 23:26:44.282836+07	2025-12-07 23:32:07.052168+07
21	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-01-08-at-10.49.24-71aa8530.jpg	gallery16	2025-12-07 23:27:14.550291+07	2025-12-07 23:32:17.226692+07
22	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-01-08-at-10.49.24-f121c2de.jpeg	Gallery17	2025-12-07 23:27:33.237506+07	2025-12-07 23:32:23.862277+07
23	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-01-08-at-10.49.23-e018ba1e.jpeg	Gallery18	2025-12-07 23:27:49.000736+07	2025-12-07 23:32:31.196797+07
24	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-01-08-at-10.49.23-fc4324bc.jpg	Gallery19	2025-12-07 23:27:59.939781+07	2025-12-07 23:32:36.670407+07
25	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-01-08-at-10.49.24-0bc2588a.jpg	Gallery20	2025-12-07 23:28:12.525362+07	2025-12-07 23:32:43.060959+07
5	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2025-04-23-at-16.31.54-fdfab938.jpg	Gallery0	2025-12-07 23:03:07.428783+07	2025-12-07 23:49:02.256115+07
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3556 (class 0 OID 35160)
=======
-- TOC entry 3560 (class 0 OID 35160)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 221
=======
-- TOC entry 3585 (class 0 OID 46468)
-- Dependencies: 229
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: mahasiswa; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.mahasiswa (id_mahasiswa, nama, tahun, status, id_admin) FROM stdin;
1901234570	Fata	2024	magang	\N
110202398	Angiek	2025	skripsi	\N
11226677	NAUFAL FALAH WAFIUDDIN	2022	skripsi	\N
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3558 (class 0 OID 35169)
=======
-- TOC entry 3562 (class 0 OID 35169)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 223
=======
-- TOC entry 3587 (class 0 OID 46477)
-- Dependencies: 231
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: member; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.member (id_member, nama, email, jabatan, foto, bidang_keahlian, notlp, deskripsi, alamat, id_admin, google_scholar) FROM stdin;
10	Dian Hanifudin Subhi, S.Kom.,M.Kom.	\N	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-05-15-at-12.17.57-89ae13e6.jpeg	\N	\N	\N	\N	\N	\N
14	Arief Prasetyo, S.Kom.,M.Kom.	arief.prasetyo@polinema.ac.id	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-01-12-at-10.57.00-35ebc528.jpeg	\N	\N	\N	\N	\N	\N
11	Moch. Zawaruddin Abdullah, S.ST., M.Kom	\N	\N	https://let.polinema.ac.id/assets/images/img-20240515.jpeg	\N	\N	\N	https://let.polinema.ac.id/assets/images/img-20240515.jpeg	\N	\N
5	Dr. Eng. Banni Satria Andoko, S.Kom.,M.MSI	ando@polinema.ac.id	Ketua Lab	https://let.polinema.ac.id/assets/images/18835562-10154469252722414-8386228144297630804-n.jpg	\N	(62) 813-5988-9181	I am a lecturer in State Polytechnic of Malang - Indonesia. My research area is Technology Enhanced Learning.	Jl. Soekarno Hatta No.9, Jatimulyo, Kec. Lowokwaru, Kota Malang, Jawa Timur	\N	https://scholar.google.com/citations?user=jetyPtUAAAAJ&hl=id
12	Putra Prima Arhandi, S.T.,M.Kom.	putraprima@polinema.ac.id	\N	https://let.polinema.ac.id/assets/images/prima-1.jpeg	\N	\N	\N	\N	\N	https://scholar.google.com/citations?user=Wl99TE0AAAAJ&hl=id
15	Dr. Eng. Afif Supiyanto	\N	analyst	https://let.polinema.ac.id/assets/images/afif.jpg	\N	\N	\N	\N	\N	https://scholar.google.com/citations?hl=id&user=VSIYb_QAAAAJ
6	Deasy Sandhya Elya Ikawati, S.Si., M.Si	deasysandhya@polinema.ac.id	member	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-01-12-at-16.32.26-97f4f829-1.jpg	\N	\N	\N	\N	\N	https://scholar.google.com/citations?hl=id&user=H-zZ_4IAAAAJ
7	Farid Angga Pribadi, S.Kom., M.Kom	faridangga@polinema.ac.id	\N	https://let.polinema.ac.id/assets/images/profpic-farid-1.jpg	\N	\N	\N	\N	\N	https://scholar.google.com/citations?hl=id&user=l2ZPw6YAAAAJ
8	Agung Nugroho Pramudhita, S.T., M.T. 	agung.pramudhita@polinema.ac.id	\N	https://let.polinema.ac.id/assets/images/whatsapp-image-2024-01-16-at-16.22.54-40cb242d.jpg	\N	\N	\N	\N	\N	https://scholar.google.com/citations?hl=id&user=hpVrzLoAAAAJ
9	Vivin Ayu Lestari, S.Pd., M.Kom	vivin@polinema.ac.id	\N	https://let.polinema.ac.id/assets/images/20230809-104340.jpeg	\N	\N	\N	\N	\N	https://scholar.google.com/citations?hl=id&user=2og3UP8AAAAJ
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3560 (class 0 OID 35178)
=======
-- TOC entry 3564 (class 0 OID 35178)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 225
=======
-- TOC entry 3589 (class 0 OID 46486)
-- Dependencies: 233
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: mitra; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.mitra (id_mitra, nama_institusi, logo) FROM stdin;
11	Hummatech	uploads/mitra/img_6935a88bef2029.53011841.png
12	Scadz.ai	uploads/mitra/img_6935a8dfe4e210.28357955.png
13	dfkiai	uploads/mitra/img_6935a920d518e8.17034906.png
10	Hiroshima	uploads/mitra/img_6935a9c300d440.87441680.png
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3574 (class 0 OID 35248)
=======
-- TOC entry 3578 (class 0 OID 35248)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 239
=======
-- TOC entry 3603 (class 0 OID 46556)
-- Dependencies: 247
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: peminjaman; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.peminjaman (id_peminjaman, id_alat, nama_peminjam, tanggal_pinjam, tanggal_kembali, status, keterangan, created_at, id_ruang, waktu_pinjam, waktu_kembali) FROM stdin;
9	9	ajiz	2025-12-07	2025-12-08	ditolak	pinjam\nRejected: sorry	2025-12-07 19:29:42.823716	\N	\N	\N
3	9	ajiz	2025-12-07	2025-12-08	ditolak	pinjam\nRejected: sorry	2025-12-07 19:40:11.827784	\N	\N	\N
6	9	ajiz	2025-12-07	2025-12-08	ditolak	pinjam\nRejected: sorry	2025-12-07 20:10:37.583853	\N	\N	\N
5	9	ajiz	2025-12-07	2025-12-08	ditolak	pinjam\nRejected: sorry	2025-12-07 20:06:54.657936	\N	\N	\N
4	9	ajiz	2025-12-07	2025-12-08	ditolak	pinjam\nRejected: sorry	2025-12-07 19:55:33.46395	\N	\N	\N
7	9	ajiz	2025-12-07	2025-12-07	dikembalikan	[APPROVED]	2025-12-07 23:49:34.291014	\N	\N	\N
11	9	ajiz	2025-12-08	2025-12-09	dipinjam	pinjam	2025-12-08 05:12:04.946621	\N	\N	\N
10	0	ajiz	2025-12-08	2025-12-09	dikembalikan	[APPROVED]	2025-12-08 05:11:31.24208	2	05:11:00	07:11:00
8	9	ajiz	2025-12-07	2025-12-09	dikembalikan	[APPROVED]	2025-12-07 23:49:59.219366	\N	\N	\N
14	9	ajiz	2025-12-11	2025-12-12	ditolak	pinjam\nRejected: maaf	2025-12-09 15:58:33.811297	\N	\N	\N
17	9	ajiz	2025-12-17	2025-12-18	ditolak	Rejected: maaf	2025-12-09 16:11:11.611531	\N	\N	\N
15	9	ajiz	2025-12-11	2025-12-09	dikembalikan	pinjam\n[APPROVED]	2025-12-09 15:59:34.846466	\N	\N	\N
12	0	alam	2025-12-10	2025-12-14	dikembalikan	pinjam\n[APPROVED]	2025-12-09 15:33:53.25584	2	17:33:00	20:33:00
13	0	kia	2025-12-10	2025-12-14	dikembalikan	pinjam lagi\n[APPROVED]	2025-12-09 15:34:55.703434	2	21:34:00	12:34:00
16	0	alam	2025-12-10	2025-12-14	dikembalikan	[APPROVED]	2025-12-09 16:01:30.270973	2	04:01:00	07:01:00
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3566 (class 0 OID 35206)
=======
-- TOC entry 3570 (class 0 OID 35206)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 231
=======
-- TOC entry 3595 (class 0 OID 46514)
-- Dependencies: 239
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: penelitian; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.penelitian (id_penelitian, id_artikel, id_mhs, judul, tahun, id_member, deskripsi, created_at, id_produk, id_mitra, tgl_mulai, tgl_selesai, id_fp) FROM stdin;
3	\N	\N	Viat Map Application	2021	\N	VIAT-map (Visual Arguments Toulmin) Application to help Reding Comprehension by using Toulmin Arguments Concept. We are trying to emphasise the logic behind a written text by adding the claim, ground and warrant following the Toulmin Argument Concept.	2025-12-09 14:33:13.036895+07	\N	\N	2021-01-09	\N	\N
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3580 (class 0 OID 35289)
=======
-- TOC entry 3584 (class 0 OID 35289)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 245
=======
-- TOC entry 3611 (class 0 OID 46597)
-- Dependencies: 255
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: pengunjung; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pengunjung (id_pengunjung, nama, email, asal_institusi, created_at, no_hp, pesan) FROM stdin;
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3568 (class 0 OID 35217)
=======
-- TOC entry 3572 (class 0 OID 35217)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 233
=======
-- TOC entry 3597 (class 0 OID 46525)
-- Dependencies: 241
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: produk; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.produk (id_produk, nama_produk, deskripsi, gambar, try) FROM stdin;
2	PseudoLearn Application	Sebuah media pembelajaran rekonstruksi algoritma pseudocode dengan menggunakan pendekatan Element Fill-in-Blank Problems di dalam pemrograman java	https://let.polinema.ac.id/assets/images/tinytake16-01-2024-05-8.png	\N
4	ALL-IN-ONE GELFREE ELECTRODE CAP BUNDLE	\N	uploads/produk/img_6935af815d8fe8.08665919.png	\N
5	ALL-IN-ONE EGG ELECTRODE CAP BUNDLE	\N	uploads/produk/img_6935afce177fe6.99916109.png	\N
3	Codeasy	\N	uploads/produk/img_6935ff193b4766.41314001.jpg	\N
1	VIAT Map Application	VIAT-map (Visual Arguments Toulmin) Application to help Reding Comprehension by using Toulmin Arguments Concept. We are trying to emphasise the logic behind a written text by adding the claim, ground and warrant following the Toulmin Argument Concept.	https://let.polinema.ac.id/assets/images/viat-map.png	https://vmap.let.polinema.ac.id/
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3576 (class 0 OID 35260)
=======
-- TOC entry 3580 (class 0 OID 35260)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 241
=======
-- TOC entry 3605 (class 0 OID 46568)
-- Dependencies: 249
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: ruang_lab; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ruang_lab (id_ruang_lab, nama_ruang, status, id_admin, created_at) FROM stdin;
3	Lab. InLET2	maintenance	4	2025-12-07 18:29:55.314899+07
2	Lab. InLET	tersedia	\N	2025-12-04 11:19:54.300741+07
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3587 (class 0 OID 42455)
=======
-- TOC entry 3591 (class 0 OID 42455)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 255
=======
-- TOC entry 3609 (class 0 OID 46586)
-- Dependencies: 253
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.settings (id_setting, site_title, site_subtitle, site_logo, created_at, updated_at, footer_logo, footer_title, copyright_text, contact_email, contact_phone, contact_address, updated_by, page_titles) FROM stdin;
1	InLET - Information And Learning Engineering Technology	State Polytechnic of Malang	\N	2025-12-04 10:20:11.300758	2025-12-04 11:10:34.350509	\N	\N	\N	\N	\N	\N	\N	{"home": {"title": "InLET - Information And Learning Engineering Technology", "subtitle": "State Polytechnic of Malang"}, "news": {"title": "News - InLET", "subtitle": "Latest Updates"}, "member": {"title": "Members - InLET", "subtitle": "Our Team"}, "research": {"title": "Research - InLET", "subtitle": "Our Research Projects"}, "guestbook": {"title": "Guestbook - InLET", "subtitle": "Leave Your Message"}, "attendance": {"title": "Attendance - InLET", "subtitle": "Track Your Attendance"}, "tool_loans": {"title": "Tool Loans - InLET", "subtitle": "Lab Equipment Rental"}}
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3582 (class 0 OID 35299)
=======
-- TOC entry 3586 (class 0 OID 35299)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 247
=======
-- TOC entry 3620 (class 0 OID 47155)
-- Dependencies: 267
-- Data for Name: video; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.video (id_video, title, href_link, created_at) FROM stdin;
1	VIAT-Map promotion Video JTI - POLINEMA	https://youtu.be/Fcv2-z0WXys	2025-12-08 09:02:44.74671+07
2	Learning Engineering Technology Lab : What is TEL?	https://youtu.be/eis5aTweBHs	2025-12-08 09:12:23.38167+07
3	Research Discussion part 4 : Dr. Eng A. Afif Supiyanto	https://youtu.be/3mX203WsBj8	2025-12-08 09:12:53.654839+07
4	Research Discussion part 5 : Aryo Pinandito, Ph.D.	https://youtu.be/l011v6Z6Yok	2025-12-08 09:13:19.498416+07
5	Learning Engineering Technology Lab : Are you ready for the new challenge?	https://youtu.be/AqYKnTCsz5A	2025-12-08 09:13:40.314615+07
\.


--
-- TOC entry 3613 (class 0 OID 46607)
-- Dependencies: 257
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Data for Name: visitor; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.visitor (id_visitor, id_pengunjung, visit_count, last_visit, first_visit, keterangan, is_read, admin_response) FROM stdin;
\.


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3612 (class 0 OID 0)
=======
-- TOC entry 3616 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 234
=======
-- TOC entry 3648 (class 0 OID 0)
-- Dependencies: 242
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: absensi_id_absensi_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.absensi_id_absensi_seq', 3, true);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3613 (class 0 OID 0)
=======
-- TOC entry 3617 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 218
=======
-- TOC entry 3649 (class 0 OID 0)
-- Dependencies: 226
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: admin_id_admin_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.admin_id_admin_seq', 4, true);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3614 (class 0 OID 0)
=======
-- TOC entry 3618 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 236
=======
-- TOC entry 3650 (class 0 OID 0)
-- Dependencies: 244
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: alat_lab_id_alat_lab_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.alat_lab_id_alat_lab_seq', 8, true);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3615 (class 0 OID 0)
=======
-- TOC entry 3619 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 228
=======
-- TOC entry 3651 (class 0 OID 0)
-- Dependencies: 236
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: artikel_id_artikel_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.artikel_id_artikel_seq', 15, true);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3616 (class 0 OID 0)
=======
-- TOC entry 3620 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 226
=======
-- TOC entry 3652 (class 0 OID 0)
-- Dependencies: 234
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: berita_id_berita_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.berita_id_berita_seq', 12, true);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3617 (class 0 OID 0)
=======
-- TOC entry 3621 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 216
=======
-- TOC entry 3653 (class 0 OID 0)
-- Dependencies: 224
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: buku_tamu_id_buku_tamu_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.buku_tamu_id_buku_tamu_seq', 5, true);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3618 (class 0 OID 0)
=======
-- TOC entry 3622 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 253
=======
-- TOC entry 3654 (class 0 OID 0)
-- Dependencies: 262
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: contact_info_id_contact_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.contact_info_id_contact_seq', 1, true);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3619 (class 0 OID 0)
=======
-- TOC entry 3623 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 242
=======
-- TOC entry 3655 (class 0 OID 0)
-- Dependencies: 250
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: fokus_penelitian_id_fp_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.fokus_penelitian_id_fp_seq', 5, true);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3620 (class 0 OID 0)
=======
-- TOC entry 3624 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 251
=======
-- TOC entry 3656 (class 0 OID 0)
-- Dependencies: 264
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: footer_settings_id_footer_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.footer_settings_id_footer_seq', 1, true);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3621 (class 0 OID 0)
=======
-- TOC entry 3625 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 214
=======
-- TOC entry 3657 (class 0 OID 0)
-- Dependencies: 222
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: gallery_id_gallery_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.gallery_id_gallery_seq', 25, true);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3622 (class 0 OID 0)
=======
-- TOC entry 3626 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 220
=======
-- TOC entry 3658 (class 0 OID 0)
-- Dependencies: 228
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: mahasiswa_id_mahasiswa_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.mahasiswa_id_mahasiswa_seq', 5, true);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3623 (class 0 OID 0)
=======
-- TOC entry 3627 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 222
=======
-- TOC entry 3659 (class 0 OID 0)
-- Dependencies: 265
-- Name: mahasiswa_nim_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.mahasiswa_nim_seq', 1, false);


--
-- TOC entry 3660 (class 0 OID 0)
-- Dependencies: 230
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: member_id_member_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.member_id_member_seq', 15, true);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3624 (class 0 OID 0)
=======
-- TOC entry 3628 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 224
=======
-- TOC entry 3661 (class 0 OID 0)
-- Dependencies: 232
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: mitra_id_mitra_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.mitra_id_mitra_seq', 13, true);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3625 (class 0 OID 0)
=======
-- TOC entry 3629 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 238
=======
-- TOC entry 3662 (class 0 OID 0)
-- Dependencies: 246
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: peminjaman_id_peminjaman_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.peminjaman_id_peminjaman_seq', 17, true);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3626 (class 0 OID 0)
=======
-- TOC entry 3630 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 230
=======
-- TOC entry 3663 (class 0 OID 0)
-- Dependencies: 238
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: penelitian_id_penelitian_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.penelitian_id_penelitian_seq', 4, true);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3627 (class 0 OID 0)
=======
-- TOC entry 3631 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 244
=======
-- TOC entry 3664 (class 0 OID 0)
-- Dependencies: 254
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: pengunjung_id_pengunjung_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pengunjung_id_pengunjung_seq', 1, false);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3628 (class 0 OID 0)
=======
-- TOC entry 3632 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 232
=======
-- TOC entry 3665 (class 0 OID 0)
-- Dependencies: 240
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: produk_id_produk_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.produk_id_produk_seq', 5, true);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3629 (class 0 OID 0)
=======
-- TOC entry 3633 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 240
=======
-- TOC entry 3666 (class 0 OID 0)
-- Dependencies: 248
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: ruang_lab_id_ruang_lab_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ruang_lab_id_ruang_lab_seq', 1, false);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3630 (class 0 OID 0)
=======
-- TOC entry 3634 (class 0 OID 0)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Dependencies: 246
=======
-- TOC entry 3667 (class 0 OID 0)
-- Dependencies: 252
-- Name: settings_id_setting_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.settings_id_setting_seq', 1, true);


--
-- TOC entry 3668 (class 0 OID 0)
-- Dependencies: 266
-- Name: video_id_video_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.video_id_video_seq', 5, true);


--
-- TOC entry 3669 (class 0 OID 0)
-- Dependencies: 256
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: visitor_id_visitor_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.visitor_id_visitor_seq', 1, false);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3364 (class 2606 OID 35234)
=======
-- TOC entry 3368 (class 2606 OID 35234)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3386 (class 2606 OID 46542)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: absensi absensi_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.absensi
    ADD CONSTRAINT absensi_pkey PRIMARY KEY (id_absensi);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3337 (class 2606 OID 35156)
=======
-- TOC entry 3341 (class 2606 OID 35156)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3359 (class 2606 OID 46464)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: admin admin_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin
    ADD CONSTRAINT admin_pkey PRIMARY KEY (id_admin);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3339 (class 2606 OID 35158)
=======
-- TOC entry 3343 (class 2606 OID 35158)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3361 (class 2606 OID 46466)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: admin admin_username_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin
    ADD CONSTRAINT admin_username_key UNIQUE (username);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3366 (class 2606 OID 35246)
=======
-- TOC entry 3370 (class 2606 OID 35246)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3388 (class 2606 OID 46554)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: alat_lab alat_lab_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.alat_lab
    ADD CONSTRAINT alat_lab_pkey PRIMARY KEY (id_alat_lab);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3354 (class 2606 OID 35204)
=======
-- TOC entry 3358 (class 2606 OID 35204)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3376 (class 2606 OID 46512)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: artikel artikel_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.artikel
    ADD CONSTRAINT artikel_pkey PRIMARY KEY (id_artikel);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3350 (class 2606 OID 35195)
=======
-- TOC entry 3354 (class 2606 OID 35195)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3372 (class 2606 OID 46503)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: berita berita_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.berita
    ADD CONSTRAINT berita_pkey PRIMARY KEY (id_berita);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3332 (class 2606 OID 35144)
=======
-- TOC entry 3336 (class 2606 OID 35144)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3354 (class 2606 OID 46452)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: buku_tamu buku_tamu_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.buku_tamu
    ADD CONSTRAINT buku_tamu_pkey PRIMARY KEY (id_buku_tamu);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3385 (class 2606 OID 42435)
=======
-- TOC entry 3389 (class 2606 OID 42435)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3407 (class 2606 OID 46854)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: contact_info contact_info_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contact_info
    ADD CONSTRAINT contact_info_pkey PRIMARY KEY (id_contact);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3376 (class 2606 OID 35276)
=======
-- TOC entry 3380 (class 2606 OID 35276)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3398 (class 2606 OID 46584)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: fokus_penelitian fokus_penelitian_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fokus_penelitian
    ADD CONSTRAINT fokus_penelitian_pkey PRIMARY KEY (id_fp);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3383 (class 2606 OID 42426)
=======
-- TOC entry 3387 (class 2606 OID 42426)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3409 (class 2606 OID 46856)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: footer_settings footer_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.footer_settings
    ADD CONSTRAINT footer_settings_pkey PRIMARY KEY (id_footer);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3328 (class 2606 OID 35131)
=======
-- TOC entry 3332 (class 2606 OID 35131)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3350 (class 2606 OID 46439)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: gallery gallery_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gallery
    ADD CONSTRAINT gallery_pkey PRIMARY KEY (id_gallery);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3342 (class 2606 OID 35542)
=======
-- TOC entry 3346 (class 2606 OID 35542)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3364 (class 2606 OID 46475)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: mahasiswa mahasiswa_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mahasiswa
    ADD CONSTRAINT mahasiswa_pkey PRIMARY KEY (id_mahasiswa);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3346 (class 2606 OID 35176)
=======
-- TOC entry 3350 (class 2606 OID 35176)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3368 (class 2606 OID 46484)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: member member_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.member
    ADD CONSTRAINT member_pkey PRIMARY KEY (id_member);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3348 (class 2606 OID 35185)
=======
-- TOC entry 3352 (class 2606 OID 35185)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3370 (class 2606 OID 46493)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: mitra mitra_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mitra
    ADD CONSTRAINT mitra_pkey PRIMARY KEY (id_mitra);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3371 (class 2606 OID 35258)
=======
-- TOC entry 3375 (class 2606 OID 35258)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3393 (class 2606 OID 46566)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: peminjaman peminjaman_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.peminjaman
    ADD CONSTRAINT peminjaman_pkey PRIMARY KEY (id_peminjaman);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3359 (class 2606 OID 35215)
=======
-- TOC entry 3363 (class 2606 OID 35215)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3381 (class 2606 OID 46523)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: penelitian penelitian_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT penelitian_pkey PRIMARY KEY (id_penelitian);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3378 (class 2606 OID 35297)
=======
-- TOC entry 3382 (class 2606 OID 35297)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3402 (class 2606 OID 46605)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: pengunjung pengunjung_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pengunjung
    ADD CONSTRAINT pengunjung_pkey PRIMARY KEY (id_pengunjung);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3362 (class 2606 OID 35224)
=======
-- TOC entry 3366 (class 2606 OID 35224)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3384 (class 2606 OID 46532)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: produk produk_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.produk
    ADD CONSTRAINT produk_pkey PRIMARY KEY (id_produk);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3374 (class 2606 OID 35267)
=======
-- TOC entry 3378 (class 2606 OID 35267)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3396 (class 2606 OID 46575)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: ruang_lab ruang_lab_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ruang_lab
    ADD CONSTRAINT ruang_lab_pkey PRIMARY KEY (id_ruang_lab);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3387 (class 2606 OID 42463)
=======
-- TOC entry 3391 (class 2606 OID 42463)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3400 (class 2606 OID 46595)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: settings settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (id_setting);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3381 (class 2606 OID 35309)
=======
-- TOC entry 3385 (class 2606 OID 35309)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3411 (class 2606 OID 47163)
-- Name: video video_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.video
    ADD CONSTRAINT video_pkey PRIMARY KEY (id_video);


--
-- TOC entry 3405 (class 2606 OID 46617)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: visitor visitor_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visitor
    ADD CONSTRAINT visitor_pkey PRIMARY KEY (id_visitor);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3367 (class 1259 OID 35315)
=======
-- TOC entry 3371 (class 1259 OID 35315)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3389 (class 1259 OID 46623)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_alatlab_id_admin; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_alatlab_id_admin ON public.alat_lab USING btree (id_admin);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3351 (class 1259 OID 35311)
=======
-- TOC entry 3355 (class 1259 OID 35311)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3373 (class 1259 OID 46619)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_berita_created_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_berita_created_at ON public.berita USING btree (created_at DESC);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3352 (class 1259 OID 35310)
=======
-- TOC entry 3356 (class 1259 OID 35310)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3374 (class 1259 OID 46618)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_berita_id_admin; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_berita_id_admin ON public.berita USING btree (id_admin);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3333 (class 1259 OID 35145)
=======
-- TOC entry 3337 (class 1259 OID 35145)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3355 (class 1259 OID 46453)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_buku_tamu_created_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_buku_tamu_created_at ON public.buku_tamu USING btree (created_at DESC);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3334 (class 1259 OID 35147)
=======
-- TOC entry 3338 (class 1259 OID 35147)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3356 (class 1259 OID 46455)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_buku_tamu_email; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_buku_tamu_email ON public.buku_tamu USING btree (email);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3335 (class 1259 OID 35146)
=======
-- TOC entry 3339 (class 1259 OID 35146)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3357 (class 1259 OID 46454)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_buku_tamu_is_read; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_buku_tamu_is_read ON public.buku_tamu USING btree (is_read);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3329 (class 1259 OID 35132)
=======
-- TOC entry 3333 (class 1259 OID 35132)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3351 (class 1259 OID 46440)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_gallery_created; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_gallery_created ON public.gallery USING btree (created_at);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3330 (class 1259 OID 35133)
=======
-- TOC entry 3334 (class 1259 OID 35133)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3352 (class 1259 OID 46441)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_gallery_id_berita; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_gallery_id_berita ON public.gallery USING btree (id_berita);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3340 (class 1259 OID 35312)
=======
-- TOC entry 3344 (class 1259 OID 35312)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3362 (class 1259 OID 46620)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_mahasiswa_id_admin; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_mahasiswa_id_admin ON public.mahasiswa USING btree (id_admin);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3343 (class 1259 OID 35313)
=======
-- TOC entry 3347 (class 1259 OID 35313)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3365 (class 1259 OID 46621)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_member_id_admin; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_member_id_admin ON public.member USING btree (id_admin);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3344 (class 1259 OID 35314)
=======
-- TOC entry 3348 (class 1259 OID 35314)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3366 (class 1259 OID 46622)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_member_nama; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_member_nama ON public.member USING btree (nama);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3368 (class 1259 OID 35316)
=======
-- TOC entry 3372 (class 1259 OID 35316)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3390 (class 1259 OID 46624)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_peminjaman_id_alat; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_peminjaman_id_alat ON public.peminjaman USING btree (id_alat);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3369 (class 1259 OID 35317)
=======
-- TOC entry 3373 (class 1259 OID 35317)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3391 (class 1259 OID 46625)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_peminjaman_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_peminjaman_status ON public.peminjaman USING btree (status);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3360 (class 1259 OID 35319)
=======
-- TOC entry 3364 (class 1259 OID 35319)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3382 (class 1259 OID 46627)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_produk_nama; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_produk_nama ON public.produk USING btree (nama_produk);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3355 (class 1259 OID 35320)
=======
-- TOC entry 3359 (class 1259 OID 35320)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3377 (class 1259 OID 46628)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_progress_artikel; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_progress_artikel ON public.penelitian USING btree (id_artikel);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3356 (class 1259 OID 35321)
=======
-- TOC entry 3360 (class 1259 OID 35321)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3378 (class 1259 OID 46629)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_progress_member; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_progress_member ON public.penelitian USING btree (id_member);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3357 (class 1259 OID 42400)
=======
-- TOC entry 3361 (class 1259 OID 42400)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3379 (class 1259 OID 46630)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_progress_mhs; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_progress_mhs ON public.penelitian USING btree (id_mhs);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3372 (class 1259 OID 35318)
=======
-- TOC entry 3376 (class 1259 OID 35318)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3394 (class 1259 OID 46626)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_ruanglab_nama; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_ruanglab_nama ON public.ruang_lab USING btree (nama_ruang);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3379 (class 1259 OID 35323)
=======
-- TOC entry 3383 (class 1259 OID 35323)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3403 (class 1259 OID 46631)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: idx_visitor_pengunjung; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_visitor_pengunjung ON public.visitor USING btree (id_pengunjung);


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3397 (class 2606 OID 35329)
=======
-- TOC entry 3401 (class 2606 OID 35329)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3416 (class 2606 OID 47252)
-- Name: artikel artikel_id_member_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.artikel
    ADD CONSTRAINT artikel_id_member_fkey FOREIGN KEY (id_member) REFERENCES public.member(id_member) ON DELETE SET NULL;


--
-- TOC entry 3417 (class 2606 OID 47262)
-- Name: artikel artikel_id_mitra_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.artikel
    ADD CONSTRAINT artikel_id_mitra_fkey FOREIGN KEY (id_mitra) REFERENCES public.mitra(id_mitra) ON DELETE SET NULL;


--
-- TOC entry 3418 (class 2606 OID 47247)
-- Name: artikel artikel_id_penelitian_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.artikel
    ADD CONSTRAINT artikel_id_penelitian_fkey FOREIGN KEY (id_penelitian) REFERENCES public.penelitian(id_penelitian) ON DELETE SET NULL;


--
-- TOC entry 3419 (class 2606 OID 47257)
-- Name: artikel artikel_id_produk_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.artikel
    ADD CONSTRAINT artikel_id_produk_fkey FOREIGN KEY (id_produk) REFERENCES public.produk(id_produk) ON DELETE SET NULL;


--
-- TOC entry 3426 (class 2606 OID 46718)
-- Name: absensi fk_absensi_mahasiswa; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.absensi
    ADD CONSTRAINT fk_absensi_mahasiswa FOREIGN KEY (id_mhs) REFERENCES public.mahasiswa(id_mahasiswa) ON DELETE RESTRICT;


--
-- TOC entry 3427 (class 2606 OID 46723)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: alat_lab fk_alatlab_admin; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.alat_lab
    ADD CONSTRAINT fk_alatlab_admin FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE RESTRICT;


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3391 (class 2606 OID 35334)
=======
-- TOC entry 3395 (class 2606 OID 35334)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3415 (class 2606 OID 46728)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: berita fk_berita_admin; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.berita
    ADD CONSTRAINT fk_berita_admin FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON DELETE SET NULL;


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3388 (class 2606 OID 35339)
=======
-- TOC entry 3392 (class 2606 OID 35339)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3412 (class 2606 OID 46733)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: gallery fk_gallery_berita; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gallery
    ADD CONSTRAINT fk_gallery_berita FOREIGN KEY (id_berita) REFERENCES public.berita(id_berita) ON UPDATE CASCADE ON DELETE CASCADE;


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3389 (class 2606 OID 35344)
=======
-- TOC entry 3393 (class 2606 OID 35344)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3413 (class 2606 OID 46738)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: mahasiswa fk_mahasiswa_admin; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mahasiswa
    ADD CONSTRAINT fk_mahasiswa_admin FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE SET NULL;


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3390 (class 2606 OID 35349)
=======
-- TOC entry 3394 (class 2606 OID 35349)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3414 (class 2606 OID 46743)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: member fk_member_admin; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.member
    ADD CONSTRAINT fk_member_admin FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE SET NULL;


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3398 (class 2606 OID 35354)
=======
-- TOC entry 3402 (class 2606 OID 35354)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3428 (class 2606 OID 46748)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: peminjaman fk_peminjaman_alat; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.peminjaman
    ADD CONSTRAINT fk_peminjaman_alat FOREIGN KEY (id_alat) REFERENCES public.alat_lab(id_alat_lab) ON UPDATE CASCADE ON DELETE RESTRICT;


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3399 (class 2606 OID 35359)
=======
-- TOC entry 3403 (class 2606 OID 35359)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3429 (class 2606 OID 46753)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: peminjaman fk_peminjaman_ruang; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.peminjaman
    ADD CONSTRAINT fk_peminjaman_ruang FOREIGN KEY (id_ruang) REFERENCES public.ruang_lab(id_ruang_lab) ON UPDATE CASCADE ON DELETE RESTRICT;


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3392 (class 2606 OID 35364)
=======
-- TOC entry 3396 (class 2606 OID 35364)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3420 (class 2606 OID 46758)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: penelitian fk_penelitian_fokus; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT fk_penelitian_fokus FOREIGN KEY (id_fp) REFERENCES public.fokus_penelitian(id_fp) ON UPDATE CASCADE ON DELETE SET NULL;


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3393 (class 2606 OID 35369)
=======
-- TOC entry 3397 (class 2606 OID 35369)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3421 (class 2606 OID 46763)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: penelitian fk_penelitian_mitra; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT fk_penelitian_mitra FOREIGN KEY (id_mitra) REFERENCES public.mitra(id_mitra) ON DELETE SET NULL;


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3394 (class 2606 OID 35374)
=======
-- TOC entry 3398 (class 2606 OID 35374)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3422 (class 2606 OID 46768)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: penelitian fk_penelitian_produk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT fk_penelitian_produk FOREIGN KEY (id_produk) REFERENCES public.produk(id_produk) ON DELETE SET NULL;


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3400 (class 2606 OID 35394)
=======
-- TOC entry 3404 (class 2606 OID 35394)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3430 (class 2606 OID 46788)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: ruang_lab fk_ruang_admin; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ruang_lab
    ADD CONSTRAINT fk_ruang_admin FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE SET NULL;


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3395 (class 2606 OID 35379)
=======
-- TOC entry 3399 (class 2606 OID 35379)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3431 (class 2606 OID 46793)
-- Name: settings fk_settings_admin; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT fk_settings_admin FOREIGN KEY (updated_by) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3423 (class 2606 OID 46773)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: penelitian progress_id_artikel_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT progress_id_artikel_fkey FOREIGN KEY (id_artikel) REFERENCES public.artikel(id_artikel) ON DELETE SET NULL;


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3396 (class 2606 OID 35384)
=======
-- TOC entry 3400 (class 2606 OID 35384)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3424 (class 2606 OID 46778)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: penelitian progress_id_member_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT progress_id_member_fkey FOREIGN KEY (id_member) REFERENCES public.member(id_member) ON DELETE SET NULL;


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3402 (class 2606 OID 42469)
=======
-- TOC entry 3406 (class 2606 OID 42469)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Name: settings settings_id_contact_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
=======
-- TOC entry 3425 (class 2606 OID 46783)
-- Name: penelitian progress_id_mhs_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT progress_id_mhs_fkey FOREIGN KEY (id_mhs) REFERENCES public.mahasiswa(id_mahasiswa) ON DELETE SET NULL;


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 3403 (class 2606 OID 42464)
=======
-- TOC entry 3407 (class 2606 OID 42464)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
-- Name: settings settings_id_footer_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_id_footer_fkey FOREIGN KEY (id_footer) REFERENCES public.footer_settings(id_footer);


--
<<<<<<< HEAD
-- TOC entry 3401 (class 2606 OID 35404)
=======
-- TOC entry 3405 (class 2606 OID 35404)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3432 (class 2606 OID 46798)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: visitor visitor_id_pengunjung_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visitor
    ADD CONSTRAINT visitor_id_pengunjung_fkey FOREIGN KEY (id_pengunjung) REFERENCES public.pengunjung(id_pengunjung) ON DELETE CASCADE;


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 2142 (class 826 OID 35120)
=======
-- TOC entry 2146 (class 826 OID 35120)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 3626 (class 0 OID 0)
-- Dependencies: 13
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: pg_database_owner
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- TOC entry 2160 (class 826 OID 46428)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: DEFAULT PRIVILEGES FOR SEQUENCES; Type: DEFAULT ACL; Schema: public; Owner: postgres
--

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON SEQUENCES  TO postgres;


--
<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- TOC entry 2141 (class 826 OID 35119)
=======
-- TOC entry 2145 (class 826 OID 35119)
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- TOC entry 2161 (class 826 OID 46427)
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: public; Owner: postgres
--

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON TABLES  TO postgres;


<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
-- Completed on 2025-12-04 08:46:39
=======
-- Completed on 2025-12-04 09:36:32
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
-- Completed on 2025-12-14 21:23:39
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql

--
-- PostgreSQL database dump complete
--

<<<<<<< HEAD:assets/inlet_pbl.sql
<<<<<<< HEAD
\unrestrict 9HCm3mc6FpSrfS1QiZsTXQoIiIxVBcSMLQU3Udc4sAOPL7lRY8bRh4hXPv59Kaz
=======
\unrestrict YM77hedTVLLAKQbTcEBGAv7TbB8ZUWNRV6xnZDqjWtocHGsfv2DPogfn4K6jPov
>>>>>>> cc6ae99129ae72cfe03f044e42a065e661bc4a54
=======
\unrestrict bpSimOsKLgSBDNW46QyuKpgMphnhkBYC8p74cGPgSMP93pGj6QJdGCrdtIA3CNe
>>>>>>> f5ee55d652d242e4963fd2e7ba8c5e287ec1fa96:assets/inlet_pbp_baruu.sql

