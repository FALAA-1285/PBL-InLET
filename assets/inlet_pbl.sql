--
-- PostgreSQL database dump
--

\restrict YM77hedTVLLAKQbTcEBGAv7TbB8ZUWNRV6xnZDqjWtocHGsfv2DPogfn4K6jPov

-- Dumped from database version 15.14
-- Dumped by pg_dump version 15.14

-- Started on 2025-12-04 09:36:32

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
-- TOC entry 269 (class 1255 OID 42479)
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
-- TOC entry 256 (class 1255 OID 42476)
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
-- TOC entry 268 (class 1255 OID 42477)
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
-- TOC entry 270 (class 1255 OID 42480)
-- Name: proc_update_absensi(integer, character varying, text); Type: PROCEDURE; Schema: public; Owner: postgres
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
-- TOC entry 3597 (class 0 OID 0)
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
-- TOC entry 3598 (class 0 OID 0)
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
-- TOC entry 3599 (class 0 OID 0)
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
-- TOC entry 3600 (class 0 OID 0)
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
-- TOC entry 3601 (class 0 OID 0)
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
-- TOC entry 3602 (class 0 OID 0)
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
-- TOC entry 3603 (class 0 OID 0)
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
-- TOC entry 3604 (class 0 OID 0)
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
-- TOC entry 3605 (class 0 OID 0)
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
-- TOC entry 3606 (class 0 OID 0)
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
-- TOC entry 3607 (class 0 OID 0)
-- Dependencies: 220
-- Name: mahasiswa_nim_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.mahasiswa_nim_seq OWNED BY public.mahasiswa.nim;


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
-- TOC entry 3608 (class 0 OID 0)
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
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mitra_id_mitra_seq OWNER TO postgres;

--
-- TOC entry 3609 (class 0 OID 0)
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
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.peminjaman_id_peminjaman_seq OWNER TO postgres;

--
-- TOC entry 3610 (class 0 OID 0)
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
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.penelitian_id_penelitian_seq OWNER TO postgres;

--
-- TOC entry 3611 (class 0 OID 0)
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
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pengunjung_id_pengunjung_seq OWNER TO postgres;

--
-- TOC entry 3612 (class 0 OID 0)
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
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.produk_id_produk_seq OWNER TO postgres;

--
-- TOC entry 3613 (class 0 OID 0)
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
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ruang_lab_id_ruang_lab_seq OWNER TO postgres;

--
-- TOC entry 3614 (class 0 OID 0)
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
-- TOC entry 250 (class 1259 OID 35424)
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
-- TOC entry 249 (class 1259 OID 35415)
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
-- TOC entry 247 (class 1259 OID 35299)
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
-- TOC entry 246 (class 1259 OID 35298)
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
-- TOC entry 3615 (class 0 OID 0)
-- Dependencies: 246
-- Name: visitor_id_visitor_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.visitor_id_visitor_seq OWNED BY public.visitor.id_visitor;


--
-- TOC entry 3305 (class 2604 OID 35229)
-- Name: absensi id_absensi; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.absensi ALTER COLUMN id_absensi SET DEFAULT nextval('public.absensi_id_absensi_seq'::regclass);


--
-- TOC entry 3291 (class 2604 OID 35152)
-- Name: admin id_admin; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin ALTER COLUMN id_admin SET DEFAULT nextval('public.admin_id_admin_seq'::regclass);


--
-- TOC entry 3307 (class 2604 OID 35239)
-- Name: alat_lab id_alat_lab; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.alat_lab ALTER COLUMN id_alat_lab SET DEFAULT nextval('public.alat_lab_id_alat_lab_seq'::regclass);


--
-- TOC entry 3300 (class 2604 OID 35200)
-- Name: artikel id_artikel; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.artikel ALTER COLUMN id_artikel SET DEFAULT nextval('public.artikel_id_artikel_seq'::regclass);


--
-- TOC entry 3298 (class 2604 OID 35190)
-- Name: berita id_berita; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.berita ALTER COLUMN id_berita SET DEFAULT nextval('public.berita_id_berita_seq'::regclass);


--
-- TOC entry 3288 (class 2604 OID 35138)
-- Name: buku_tamu id_buku_tamu; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.buku_tamu ALTER COLUMN id_buku_tamu SET DEFAULT nextval('public.buku_tamu_id_buku_tamu_seq'::regclass);


--
-- TOC entry 3325 (class 2604 OID 42431)
-- Name: contact_info id_contact; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contact_info ALTER COLUMN id_contact SET DEFAULT nextval('public.contact_info_id_contact_seq'::regclass);


--
-- TOC entry 3317 (class 2604 OID 35272)
-- Name: fokus_penelitian id_fp; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fokus_penelitian ALTER COLUMN id_fp SET DEFAULT nextval('public.fokus_penelitian_id_fp_seq'::regclass);


--
-- TOC entry 3324 (class 2604 OID 42422)
-- Name: footer_settings id_footer; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.footer_settings ALTER COLUMN id_footer SET DEFAULT nextval('public.footer_settings_id_footer_seq'::regclass);


--
-- TOC entry 3285 (class 2604 OID 35125)
-- Name: gallery id_gallery; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gallery ALTER COLUMN id_gallery SET DEFAULT nextval('public.gallery_id_gallery_seq'::regclass);


--
-- TOC entry 3294 (class 2604 OID 35536)
-- Name: mahasiswa nim; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mahasiswa ALTER COLUMN nim SET DEFAULT nextval('public.mahasiswa_nim_seq'::regclass);


--
-- TOC entry 3296 (class 2604 OID 35172)
-- Name: member id_member; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.member ALTER COLUMN id_member SET DEFAULT nextval('public.member_id_member_seq'::regclass);


--
-- TOC entry 3297 (class 2604 OID 35181)
-- Name: mitra id_mitra; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mitra ALTER COLUMN id_mitra SET DEFAULT nextval('public.mitra_id_mitra_seq'::regclass);


--
-- TOC entry 3311 (class 2604 OID 35251)
-- Name: peminjaman id_peminjaman; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.peminjaman ALTER COLUMN id_peminjaman SET DEFAULT nextval('public.peminjaman_id_peminjaman_seq'::regclass);


--
-- TOC entry 3301 (class 2604 OID 35209)
-- Name: penelitian id_penelitian; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian ALTER COLUMN id_penelitian SET DEFAULT nextval('public.penelitian_id_penelitian_seq'::regclass);


--
-- TOC entry 3318 (class 2604 OID 35292)
-- Name: pengunjung id_pengunjung; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pengunjung ALTER COLUMN id_pengunjung SET DEFAULT nextval('public.pengunjung_id_pengunjung_seq'::regclass);


--
-- TOC entry 3304 (class 2604 OID 35220)
-- Name: produk id_produk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.produk ALTER COLUMN id_produk SET DEFAULT nextval('public.produk_id_produk_seq'::regclass);


--
-- TOC entry 3314 (class 2604 OID 35263)
-- Name: ruang_lab id_ruang_lab; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ruang_lab ALTER COLUMN id_ruang_lab SET DEFAULT nextval('public.ruang_lab_id_ruang_lab_seq'::regclass);


--
-- TOC entry 3320 (class 2604 OID 35302)
-- Name: visitor id_visitor; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visitor ALTER COLUMN id_visitor SET DEFAULT nextval('public.visitor_id_visitor_seq'::regclass);


--
-- TOC entry 3574 (class 0 OID 35226)
-- Dependencies: 235
-- Data for Name: absensi; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.absensi (id_absensi, nim, waktu_datang, waktu_pulang, keterangan, tanggal) FROM stdin;
\.


--
-- TOC entry 3558 (class 0 OID 35149)
-- Dependencies: 219
-- Data for Name: admin; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.admin (id_admin, username, password_hash, role, created_at) FROM stdin;
4	admin	$2y$10$zYpf47dAlg0u4ABEqKpCGur7EIgbtGFNX5EPLNwDaqAZGVqH2aiB6	admin	2025-12-02 13:11:36.651666+07
\.


--
-- TOC entry 3576 (class 0 OID 35236)
-- Dependencies: 237
-- Data for Name: alat_lab; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.alat_lab (id_alat_lab, nama_alat, deskripsi, stock, created_at, updated_at, id_admin) FROM stdin;
\.


--
-- TOC entry 3568 (class 0 OID 35197)
-- Dependencies: 229
-- Data for Name: artikel; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.artikel (id_artikel, judul, tahun, konten) FROM stdin;
\.


--
-- TOC entry 3566 (class 0 OID 35187)
-- Dependencies: 227
-- Data for Name: berita; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.berita (id_berita, judul, konten, gambar_thumbnail, created_at, id_admin) FROM stdin;
\.


--
-- TOC entry 3556 (class 0 OID 35135)
-- Dependencies: 217
-- Data for Name: buku_tamu; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.buku_tamu (id_buku_tamu, nama, email, institusi, no_hp, pesan, created_at, is_read, admin_response) FROM stdin;
\.


--
-- TOC entry 3590 (class 0 OID 42428)
-- Dependencies: 254
-- Data for Name: contact_info; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.contact_info (id_contact, contact_email, contact_phone, contact_address) FROM stdin;
1	info@inlet.edu	+62 823 328 645	Malang, East Java
\.


--
-- TOC entry 3582 (class 0 OID 35269)
-- Dependencies: 243
-- Data for Name: fokus_penelitian; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.fokus_penelitian (id_fp, title, deskripsi, detail) FROM stdin;
\.


--
-- TOC entry 3588 (class 0 OID 42419)
-- Dependencies: 252
-- Data for Name: footer_settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.footer_settings (id_footer, footer_logo, footer_title, footer_subtitle, copyright_text) FROM stdin;
1	uploads/settings/img_692e5d9b52f897.08161504.png	Information and Learning Engineering	\N	© 2025 InLET - Information and Learning Engineering Technology
\.


--
-- TOC entry 3554 (class 0 OID 35122)
-- Dependencies: 215
-- Data for Name: gallery; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.gallery (id_gallery, id_berita, gambar, judul, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3560 (class 0 OID 35160)
-- Dependencies: 221
-- Data for Name: mahasiswa; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.mahasiswa (nim, nama, tahun, status, id_admin) FROM stdin;
\.


--
-- TOC entry 3562 (class 0 OID 35169)
-- Dependencies: 223
-- Data for Name: member; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.member (id_member, nama, email, jabatan, foto, bidang_keahlian, notlp, deskripsi, alamat, id_admin) FROM stdin;
\.


--
-- TOC entry 3564 (class 0 OID 35178)
-- Dependencies: 225
-- Data for Name: mitra; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.mitra (id_mitra, nama_institusi, logo) FROM stdin;
\.


--
-- TOC entry 3578 (class 0 OID 35248)
-- Dependencies: 239
-- Data for Name: peminjaman; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.peminjaman (id_peminjaman, id_alat, nama_peminjam, tanggal_pinjam, tanggal_kembali, status, keterangan, created_at, id_ruang, waktu_pinjam, waktu_kembali) FROM stdin;
\.


--
-- TOC entry 3570 (class 0 OID 35206)
-- Dependencies: 231
-- Data for Name: penelitian; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.penelitian (id_penelitian, id_artikel, nim, judul, tahun, id_member, deskripsi, created_at, id_produk, id_mitra, tgl_mulai, tgl_selesai, id_fp) FROM stdin;
\.


--
-- TOC entry 3584 (class 0 OID 35289)
-- Dependencies: 245
-- Data for Name: pengunjung; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pengunjung (id_pengunjung, nama, email, asal_institusi, created_at, no_hp, pesan) FROM stdin;
\.


--
-- TOC entry 3572 (class 0 OID 35217)
-- Dependencies: 233
-- Data for Name: produk; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.produk (id_produk, nama_produk, deskripsi) FROM stdin;
\.


--
-- TOC entry 3580 (class 0 OID 35260)
-- Dependencies: 241
-- Data for Name: ruang_lab; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ruang_lab (id_ruang_lab, nama_ruang, status, id_admin, created_at) FROM stdin;
\.


--
-- TOC entry 3591 (class 0 OID 42455)
-- Dependencies: 255
-- Data for Name: settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.settings (id_setting, site_title, site_subtitle, site_logo, created_at, updated_at, updated_by, id_footer, id_contact, page_titles, footer_logo, footer_title, copyright_text, contact_email, contact_phone, contact_address) FROM stdin;
1	InLET - Information And Learning Engineering Technology	Transforming the future of language learning through advanced engineering.	uploads/settings/img_692e5d9b524999.72620066.png	2025-12-02 10:24:58.64899	2025-12-02 10:36:41.173886	\N	1	1	{}	\N	\N	\N	\N	\N	\N
2	Research - Information And Learning Engineering Technology	Pioneering advancements in Language and Educational Technology to shape the future of learning	uploads/settings/img_692e5d9b524999.72620066.png	2025-12-02 10:24:58.64899	2025-12-02 10:36:41.173886	\N	1	1	{}	\N	\N	\N	\N	\N	\N
3	Our Experts - Information And Learning Engineering Technology	Driving innovation in Information and Learning Engineering Technology.	uploads/settings/img_692e5d9b524999.72620066.png	2025-12-02 10:24:58.64899	2025-12-02 10:36:41.173886	\N	1	1	{}	\N	\N	\N	\N	\N	\N
4	News - Information And Learning Engineering Technology	Stay updated with our latest publications, activities, and breakthroughs.	uploads/settings/img_692e5d9b524999.72620066.png	2025-12-02 10:24:58.64899	2025-12-02 10:36:41.173886	\N	1	1	{}	\N	\N	\N	\N	\N	\N
5	Lab Borrowing Dashboard	Easily manage tool and room borrowing from the lab	uploads/settings/img_692e5d9b524999.72620066.png	2025-12-02 10:24:58.64899	2025-12-02 10:36:41.173886	\N	1	1	{}	\N	\N	\N	\N	\N	\N
6	Attendance Form	Information And Learning Engineering Technology	uploads/settings/img_692e5d9b524999.72620066.png	2025-12-02 10:24:58.64899	2025-12-02 10:36:41.173886	\N	1	1	{}	\N	\N	\N	\N	\N	\N
7	Guestbook	Share your feedback, suggestions, or messages for Lab InLET	uploads/settings/img_692e5d9b524999.72620066.png	2025-12-02 10:24:58.64899	2025-12-02 10:36:41.173886	\N	1	1	{}	\N	\N	\N	\N	\N	\N
\.


--
-- TOC entry 3586 (class 0 OID 35299)
-- Dependencies: 247
-- Data for Name: visitor; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.visitor (id_visitor, id_pengunjung, visit_count, last_visit, first_visit, keterangan, is_read, admin_response) FROM stdin;
\.


--
-- TOC entry 3616 (class 0 OID 0)
-- Dependencies: 234
-- Name: absensi_id_absensi_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.absensi_id_absensi_seq', 1, false);


--
-- TOC entry 3617 (class 0 OID 0)
-- Dependencies: 218
-- Name: admin_id_admin_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.admin_id_admin_seq', 4, true);


--
-- TOC entry 3618 (class 0 OID 0)
-- Dependencies: 236
-- Name: alat_lab_id_alat_lab_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.alat_lab_id_alat_lab_seq', 8, true);


--
-- TOC entry 3619 (class 0 OID 0)
-- Dependencies: 228
-- Name: artikel_id_artikel_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.artikel_id_artikel_seq', 3, true);


--
-- TOC entry 3620 (class 0 OID 0)
-- Dependencies: 226
-- Name: berita_id_berita_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.berita_id_berita_seq', 2, true);


--
-- TOC entry 3621 (class 0 OID 0)
-- Dependencies: 216
-- Name: buku_tamu_id_buku_tamu_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.buku_tamu_id_buku_tamu_seq', 3, true);


--
-- TOC entry 3622 (class 0 OID 0)
-- Dependencies: 253
-- Name: contact_info_id_contact_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.contact_info_id_contact_seq', 1, true);


--
-- TOC entry 3623 (class 0 OID 0)
-- Dependencies: 242
-- Name: fokus_penelitian_id_fp_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.fokus_penelitian_id_fp_seq', 1, false);


--
-- TOC entry 3624 (class 0 OID 0)
-- Dependencies: 251
-- Name: footer_settings_id_footer_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.footer_settings_id_footer_seq', 1, true);


--
-- TOC entry 3625 (class 0 OID 0)
-- Dependencies: 214
-- Name: gallery_id_gallery_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.gallery_id_gallery_seq', 4, true);


--
-- TOC entry 3626 (class 0 OID 0)
-- Dependencies: 220
-- Name: mahasiswa_id_mahasiswa_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.mahasiswa_id_mahasiswa_seq', 5, true);


--
-- TOC entry 3627 (class 0 OID 0)
-- Dependencies: 222
-- Name: member_id_member_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.member_id_member_seq', 3, true);


--
-- TOC entry 3628 (class 0 OID 0)
-- Dependencies: 224
-- Name: mitra_id_mitra_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.mitra_id_mitra_seq', 9, true);


--
-- TOC entry 3629 (class 0 OID 0)
-- Dependencies: 238
-- Name: peminjaman_id_peminjaman_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.peminjaman_id_peminjaman_seq', 2, true);


--
-- TOC entry 3630 (class 0 OID 0)
-- Dependencies: 230
-- Name: penelitian_id_penelitian_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.penelitian_id_penelitian_seq', 2, true);


--
-- TOC entry 3631 (class 0 OID 0)
-- Dependencies: 244
-- Name: pengunjung_id_pengunjung_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pengunjung_id_pengunjung_seq', 1, false);


--
-- TOC entry 3632 (class 0 OID 0)
-- Dependencies: 232
-- Name: produk_id_produk_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.produk_id_produk_seq', 1, false);


--
-- TOC entry 3633 (class 0 OID 0)
-- Dependencies: 240
-- Name: ruang_lab_id_ruang_lab_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ruang_lab_id_ruang_lab_seq', 1, false);


--
-- TOC entry 3634 (class 0 OID 0)
-- Dependencies: 246
-- Name: visitor_id_visitor_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.visitor_id_visitor_seq', 1, false);


--
-- TOC entry 3368 (class 2606 OID 35234)
-- Name: absensi absensi_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.absensi
    ADD CONSTRAINT absensi_pkey PRIMARY KEY (id_absensi);


--
-- TOC entry 3341 (class 2606 OID 35156)
-- Name: admin admin_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin
    ADD CONSTRAINT admin_pkey PRIMARY KEY (id_admin);


--
-- TOC entry 3343 (class 2606 OID 35158)
-- Name: admin admin_username_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin
    ADD CONSTRAINT admin_username_key UNIQUE (username);


--
-- TOC entry 3370 (class 2606 OID 35246)
-- Name: alat_lab alat_lab_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.alat_lab
    ADD CONSTRAINT alat_lab_pkey PRIMARY KEY (id_alat_lab);


--
-- TOC entry 3358 (class 2606 OID 35204)
-- Name: artikel artikel_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.artikel
    ADD CONSTRAINT artikel_pkey PRIMARY KEY (id_artikel);


--
-- TOC entry 3354 (class 2606 OID 35195)
-- Name: berita berita_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.berita
    ADD CONSTRAINT berita_pkey PRIMARY KEY (id_berita);


--
-- TOC entry 3336 (class 2606 OID 35144)
-- Name: buku_tamu buku_tamu_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.buku_tamu
    ADD CONSTRAINT buku_tamu_pkey PRIMARY KEY (id_buku_tamu);


--
-- TOC entry 3389 (class 2606 OID 42435)
-- Name: contact_info contact_info_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contact_info
    ADD CONSTRAINT contact_info_pkey PRIMARY KEY (id_contact);


--
-- TOC entry 3380 (class 2606 OID 35276)
-- Name: fokus_penelitian fokus_penelitian_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fokus_penelitian
    ADD CONSTRAINT fokus_penelitian_pkey PRIMARY KEY (id_fp);


--
-- TOC entry 3387 (class 2606 OID 42426)
-- Name: footer_settings footer_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.footer_settings
    ADD CONSTRAINT footer_settings_pkey PRIMARY KEY (id_footer);


--
-- TOC entry 3332 (class 2606 OID 35131)
-- Name: gallery gallery_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gallery
    ADD CONSTRAINT gallery_pkey PRIMARY KEY (id_gallery);


--
-- TOC entry 3346 (class 2606 OID 35542)
-- Name: mahasiswa mahasiswa_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mahasiswa
    ADD CONSTRAINT mahasiswa_pkey PRIMARY KEY (nim);


--
-- TOC entry 3350 (class 2606 OID 35176)
-- Name: member member_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.member
    ADD CONSTRAINT member_pkey PRIMARY KEY (id_member);


--
-- TOC entry 3352 (class 2606 OID 35185)
-- Name: mitra mitra_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mitra
    ADD CONSTRAINT mitra_pkey PRIMARY KEY (id_mitra);


--
-- TOC entry 3375 (class 2606 OID 35258)
-- Name: peminjaman peminjaman_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.peminjaman
    ADD CONSTRAINT peminjaman_pkey PRIMARY KEY (id_peminjaman);


--
-- TOC entry 3363 (class 2606 OID 35215)
-- Name: penelitian penelitian_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT penelitian_pkey PRIMARY KEY (id_penelitian);


--
-- TOC entry 3382 (class 2606 OID 35297)
-- Name: pengunjung pengunjung_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pengunjung
    ADD CONSTRAINT pengunjung_pkey PRIMARY KEY (id_pengunjung);


--
-- TOC entry 3366 (class 2606 OID 35224)
-- Name: produk produk_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.produk
    ADD CONSTRAINT produk_pkey PRIMARY KEY (id_produk);


--
-- TOC entry 3378 (class 2606 OID 35267)
-- Name: ruang_lab ruang_lab_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ruang_lab
    ADD CONSTRAINT ruang_lab_pkey PRIMARY KEY (id_ruang_lab);


--
-- TOC entry 3391 (class 2606 OID 42463)
-- Name: settings settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (id_setting);


--
-- TOC entry 3385 (class 2606 OID 35309)
-- Name: visitor visitor_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visitor
    ADD CONSTRAINT visitor_pkey PRIMARY KEY (id_visitor);


--
-- TOC entry 3371 (class 1259 OID 35315)
-- Name: idx_alatlab_id_admin; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_alatlab_id_admin ON public.alat_lab USING btree (id_admin);


--
-- TOC entry 3355 (class 1259 OID 35311)
-- Name: idx_berita_created_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_berita_created_at ON public.berita USING btree (created_at DESC);


--
-- TOC entry 3356 (class 1259 OID 35310)
-- Name: idx_berita_id_admin; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_berita_id_admin ON public.berita USING btree (id_admin);


--
-- TOC entry 3337 (class 1259 OID 35145)
-- Name: idx_buku_tamu_created_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_buku_tamu_created_at ON public.buku_tamu USING btree (created_at DESC);


--
-- TOC entry 3338 (class 1259 OID 35147)
-- Name: idx_buku_tamu_email; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_buku_tamu_email ON public.buku_tamu USING btree (email);


--
-- TOC entry 3339 (class 1259 OID 35146)
-- Name: idx_buku_tamu_is_read; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_buku_tamu_is_read ON public.buku_tamu USING btree (is_read);


--
-- TOC entry 3333 (class 1259 OID 35132)
-- Name: idx_gallery_created; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_gallery_created ON public.gallery USING btree (created_at);


--
-- TOC entry 3334 (class 1259 OID 35133)
-- Name: idx_gallery_id_berita; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_gallery_id_berita ON public.gallery USING btree (id_berita);


--
-- TOC entry 3344 (class 1259 OID 35312)
-- Name: idx_mahasiswa_id_admin; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_mahasiswa_id_admin ON public.mahasiswa USING btree (id_admin);


--
-- TOC entry 3347 (class 1259 OID 35313)
-- Name: idx_member_id_admin; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_member_id_admin ON public.member USING btree (id_admin);


--
-- TOC entry 3348 (class 1259 OID 35314)
-- Name: idx_member_nama; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_member_nama ON public.member USING btree (nama);


--
-- TOC entry 3372 (class 1259 OID 35316)
-- Name: idx_peminjaman_id_alat; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_peminjaman_id_alat ON public.peminjaman USING btree (id_alat);


--
-- TOC entry 3373 (class 1259 OID 35317)
-- Name: idx_peminjaman_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_peminjaman_status ON public.peminjaman USING btree (status);


--
-- TOC entry 3364 (class 1259 OID 35319)
-- Name: idx_produk_nama; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_produk_nama ON public.produk USING btree (nama_produk);


--
-- TOC entry 3359 (class 1259 OID 35320)
-- Name: idx_progress_artikel; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_progress_artikel ON public.penelitian USING btree (id_artikel);


--
-- TOC entry 3360 (class 1259 OID 35321)
-- Name: idx_progress_member; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_progress_member ON public.penelitian USING btree (id_member);


--
-- TOC entry 3361 (class 1259 OID 42400)
-- Name: idx_progress_mhs; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_progress_mhs ON public.penelitian USING btree (nim);


--
-- TOC entry 3376 (class 1259 OID 35318)
-- Name: idx_ruanglab_nama; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_ruanglab_nama ON public.ruang_lab USING btree (nama_ruang);


--
-- TOC entry 3383 (class 1259 OID 35323)
-- Name: idx_visitor_pengunjung; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_visitor_pengunjung ON public.visitor USING btree (id_pengunjung);


--
-- TOC entry 3401 (class 2606 OID 35329)
-- Name: alat_lab fk_alatlab_admin; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.alat_lab
    ADD CONSTRAINT fk_alatlab_admin FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3395 (class 2606 OID 35334)
-- Name: berita fk_berita_admin; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.berita
    ADD CONSTRAINT fk_berita_admin FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON DELETE SET NULL;


--
-- TOC entry 3392 (class 2606 OID 35339)
-- Name: gallery fk_gallery_berita; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gallery
    ADD CONSTRAINT fk_gallery_berita FOREIGN KEY (id_berita) REFERENCES public.berita(id_berita) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3393 (class 2606 OID 35344)
-- Name: mahasiswa fk_mahasiswa_admin; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mahasiswa
    ADD CONSTRAINT fk_mahasiswa_admin FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3394 (class 2606 OID 35349)
-- Name: member fk_member_admin; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.member
    ADD CONSTRAINT fk_member_admin FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3402 (class 2606 OID 35354)
-- Name: peminjaman fk_peminjaman_alat; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.peminjaman
    ADD CONSTRAINT fk_peminjaman_alat FOREIGN KEY (id_alat) REFERENCES public.alat_lab(id_alat_lab) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3403 (class 2606 OID 35359)
-- Name: peminjaman fk_peminjaman_ruang; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.peminjaman
    ADD CONSTRAINT fk_peminjaman_ruang FOREIGN KEY (id_ruang) REFERENCES public.ruang_lab(id_ruang_lab) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3396 (class 2606 OID 35364)
-- Name: penelitian fk_penelitian_fokus; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT fk_penelitian_fokus FOREIGN KEY (id_fp) REFERENCES public.fokus_penelitian(id_fp) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3397 (class 2606 OID 35369)
-- Name: penelitian fk_penelitian_mitra; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT fk_penelitian_mitra FOREIGN KEY (id_mitra) REFERENCES public.mitra(id_mitra) ON DELETE SET NULL;


--
-- TOC entry 3398 (class 2606 OID 35374)
-- Name: penelitian fk_penelitian_produk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT fk_penelitian_produk FOREIGN KEY (id_produk) REFERENCES public.produk(id_produk) ON DELETE SET NULL;


--
-- TOC entry 3404 (class 2606 OID 35394)
-- Name: ruang_lab fk_ruang_admin; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ruang_lab
    ADD CONSTRAINT fk_ruang_admin FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3399 (class 2606 OID 35379)
-- Name: penelitian progress_id_artikel_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT progress_id_artikel_fkey FOREIGN KEY (id_artikel) REFERENCES public.artikel(id_artikel) ON DELETE SET NULL;


--
-- TOC entry 3400 (class 2606 OID 35384)
-- Name: penelitian progress_id_member_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT progress_id_member_fkey FOREIGN KEY (id_member) REFERENCES public.member(id_member) ON DELETE SET NULL;


--
-- TOC entry 3406 (class 2606 OID 42469)
-- Name: settings settings_id_contact_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_id_contact_fkey FOREIGN KEY (id_contact) REFERENCES public.contact_info(id_contact);


--
-- TOC entry 3407 (class 2606 OID 42464)
-- Name: settings settings_id_footer_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_id_footer_fkey FOREIGN KEY (id_footer) REFERENCES public.footer_settings(id_footer);


--
-- TOC entry 3405 (class 2606 OID 35404)
-- Name: visitor visitor_id_pengunjung_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visitor
    ADD CONSTRAINT visitor_id_pengunjung_fkey FOREIGN KEY (id_pengunjung) REFERENCES public.pengunjung(id_pengunjung) ON DELETE CASCADE;


--
-- TOC entry 2146 (class 826 OID 35120)
-- Name: DEFAULT PRIVILEGES FOR SEQUENCES; Type: DEFAULT ACL; Schema: public; Owner: postgres
--

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON SEQUENCES  TO postgres;


--
-- TOC entry 2145 (class 826 OID 35119)
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: public; Owner: postgres
--

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON TABLES  TO postgres;


-- Completed on 2025-12-04 09:36:32

--
-- PostgreSQL database dump complete
--

\unrestrict YM77hedTVLLAKQbTcEBGAv7TbB8ZUWNRV6xnZDqjWtocHGsfv2DPogfn4K6jPov

