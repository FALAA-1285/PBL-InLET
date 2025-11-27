--
-- PostgreSQL database dump
--

\restrict KK6d3eiVNOHlF8FXisPNWk0Zy2fkjz3UnFUJbDOPaBnhsAGbjfOtIKBbK7JTyOZ

-- Dumped from database version 15.14
-- Dumped by pg_dump version 15.14

-- Started on 2025-11-27 10:31:03

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
-- TOC entry 251 (class 1255 OID 35332)
-- Name: tambah_artikel(character varying, integer, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.tambah_artikel(p_judul character varying, p_tahun integer, p_konten character varying) RETURNS void
    LANGUAGE plpgsql
    AS $$
            BEGIN
                INSERT INTO artikel (judul, tahun, konten)
                VALUES (p_judul, p_tahun, p_konten);
            END;
            $$;


ALTER FUNCTION public.tambah_artikel(p_judul character varying, p_tahun integer, p_konten character varying) OWNER TO postgres;

--
-- TOC entry 252 (class 1255 OID 35333)
-- Name: tambah_member(character varying, character varying, character varying, character varying, character varying, character varying, text, text, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.tambah_member(p_nama character varying, p_email character varying, p_jabatan character varying, p_foto character varying, p_keahlian character varying, p_notlp character varying, p_deskripsi text, p_alamat text, p_id_admin integer) RETURNS void
    LANGUAGE plpgsql
    AS $$
            BEGIN
                INSERT INTO member (
                    nama, email, jabatan, foto, bidang_keahlian,
                    notlp, deskripsi, alamat, id_admin
                )
                VALUES (
                    p_nama, p_email, p_jabatan, p_foto, p_keahlian,
                    p_notlp, p_deskripsi, p_alamat, p_id_admin
                );
            END;
            $$;


ALTER FUNCTION public.tambah_member(p_nama character varying, p_email character varying, p_jabatan character varying, p_foto character varying, p_keahlian character varying, p_notlp character varying, p_deskripsi text, p_alamat text, p_id_admin integer) OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 214 (class 1259 OID 35045)
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
-- TOC entry 215 (class 1259 OID 35051)
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
-- TOC entry 3564 (class 0 OID 0)
-- Dependencies: 215
-- Name: absensi_id_absensi_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.absensi_id_absensi_seq OWNED BY public.absensi.id_absensi;


--
-- TOC entry 216 (class 1259 OID 35052)
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
-- TOC entry 217 (class 1259 OID 35057)
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
-- TOC entry 3565 (class 0 OID 0)
-- Dependencies: 217
-- Name: admin_id_admin_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.admin_id_admin_seq OWNED BY public.admin.id_admin;


--
-- TOC entry 218 (class 1259 OID 35058)
-- Name: alat_lab; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.alat_lab (
    id_alat integer NOT NULL,
    nama_alat character varying(255) NOT NULL,
    deskripsi text,
    stock integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    id_admin integer
);


ALTER TABLE public.alat_lab OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 35066)
-- Name: alat_lab_id_alat_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.alat_lab_id_alat_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.alat_lab_id_alat_seq OWNER TO postgres;

--
-- TOC entry 3566 (class 0 OID 0)
-- Dependencies: 219
-- Name: alat_lab_id_alat_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.alat_lab_id_alat_seq OWNED BY public.alat_lab.id_alat;


--
-- TOC entry 220 (class 1259 OID 35067)
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
-- TOC entry 221 (class 1259 OID 35072)
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
-- TOC entry 3567 (class 0 OID 0)
-- Dependencies: 221
-- Name: artikel_id_artikel_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.artikel_id_artikel_seq OWNED BY public.artikel.id_artikel;


--
-- TOC entry 222 (class 1259 OID 35073)
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
-- TOC entry 223 (class 1259 OID 35079)
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
-- TOC entry 3568 (class 0 OID 0)
-- Dependencies: 223
-- Name: berita_id_berita_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.berita_id_berita_seq OWNED BY public.berita.id_berita;


--
-- TOC entry 249 (class 1259 OID 35343)
-- Name: buku_tamu; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.buku_tamu (
    id_buku_tamu integer NOT NULL,
    nama character varying(150) NOT NULL,
    email character varying(150) NOT NULL,
    institusi character varying(200),
    no_hp character varying(50),
    pesan character varying(2000),
    created_at timestamp with time zone DEFAULT now(),
    is_read boolean DEFAULT false,
    admin_response character varying(2000)
);


ALTER TABLE public.buku_tamu OWNER TO postgres;

--
-- TOC entry 248 (class 1259 OID 35342)
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
-- TOC entry 3569 (class 0 OID 0)
-- Dependencies: 248
-- Name: buku_tamu_id_buku_tamu_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.buku_tamu_id_buku_tamu_seq OWNED BY public.buku_tamu.id_buku_tamu;


--
-- TOC entry 243 (class 1259 OID 35282)
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
-- TOC entry 244 (class 1259 OID 35287)
-- Name: fokus_penelitian_id_fr_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fokus_penelitian_id_fr_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.fokus_penelitian_id_fr_seq OWNER TO postgres;

--
-- TOC entry 3570 (class 0 OID 0)
-- Dependencies: 244
-- Name: fokus_penelitian_id_fr_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fokus_penelitian_id_fr_seq OWNED BY public.fokus_penelitian.id_fp;


--
-- TOC entry 224 (class 1259 OID 35080)
-- Name: gallery; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.gallery (
    id_gallery integer NOT NULL,
    id_berita integer NOT NULL,
    gambar character varying(255) NOT NULL,
    judul character varying(255),
    deskripsi text,
    urutan integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.gallery OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 35088)
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
-- TOC entry 3571 (class 0 OID 0)
-- Dependencies: 225
-- Name: gallery_id_gallery_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.gallery_id_gallery_seq OWNED BY public.gallery.id_gallery;


--
-- TOC entry 226 (class 1259 OID 35089)
-- Name: mahasiswa; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mahasiswa (
    id_mhs integer NOT NULL,
    nama character varying(150) NOT NULL,
    title character varying(200),
    tahun integer,
    status character varying(20) DEFAULT 'regular'::character varying NOT NULL,
    id_admin integer,
    CONSTRAINT chk_mahasiswa_status CHECK (((status)::text = ANY (ARRAY[('magang'::character varying)::text, ('skripsi'::character varying)::text, ('regular'::character varying)::text])))
);


ALTER TABLE public.mahasiswa OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 35094)
-- Name: mahasiswa_id_mhs_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.mahasiswa_id_mhs_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mahasiswa_id_mhs_seq OWNER TO postgres;

--
-- TOC entry 3572 (class 0 OID 0)
-- Dependencies: 227
-- Name: mahasiswa_id_mhs_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.mahasiswa_id_mhs_seq OWNED BY public.mahasiswa.id_mhs;


--
-- TOC entry 228 (class 1259 OID 35095)
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
-- TOC entry 229 (class 1259 OID 35100)
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
-- TOC entry 3573 (class 0 OID 0)
-- Dependencies: 229
-- Name: member_id_member_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.member_id_member_seq OWNED BY public.member.id_member;


--
-- TOC entry 230 (class 1259 OID 35101)
-- Name: mitra; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mitra (
    id_mitra integer NOT NULL,
    nama_institusi character varying(255) NOT NULL,
    logo character varying(255)
);


ALTER TABLE public.mitra OWNER TO postgres;

--
-- TOC entry 231 (class 1259 OID 35106)
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
-- TOC entry 3574 (class 0 OID 0)
-- Dependencies: 231
-- Name: mitra_id_mitra_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.mitra_id_mitra_seq OWNED BY public.mitra.id_mitra;


--
-- TOC entry 232 (class 1259 OID 35107)
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
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.peminjaman OWNER TO postgres;

--
-- TOC entry 233 (class 1259 OID 35114)
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
-- TOC entry 3575 (class 0 OID 0)
-- Dependencies: 233
-- Name: peminjaman_id_peminjaman_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.peminjaman_id_peminjaman_seq OWNED BY public.peminjaman.id_peminjaman;


--
-- TOC entry 234 (class 1259 OID 35115)
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
    tgl_selesai date
);


ALTER TABLE public.penelitian OWNER TO postgres;

--
-- TOC entry 235 (class 1259 OID 35122)
-- Name: pengunjung; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pengunjung (
    id_pengunjung integer NOT NULL,
    nama character varying(150),
    email character varying(150),
    institusi character varying(200),
    no_hp character varying(50),
    pesan character varying(2000),
    created_at timestamp with time zone DEFAULT now(),
    is_read boolean DEFAULT false,
    admin_response character varying(2000)
);


ALTER TABLE public.pengunjung OWNER TO postgres;

--
-- TOC entry 236 (class 1259 OID 35129)
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
-- TOC entry 3576 (class 0 OID 0)
-- Dependencies: 236
-- Name: pengunjung_id_pengunjung_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pengunjung_id_pengunjung_seq OWNED BY public.pengunjung.id_pengunjung;


--
-- TOC entry 237 (class 1259 OID 35130)
-- Name: produk; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.produk (
    id_produk integer NOT NULL,
    nama_produk character varying(255) NOT NULL,
    deskripsi text
);


ALTER TABLE public.produk OWNER TO postgres;

--
-- TOC entry 238 (class 1259 OID 35135)
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
-- TOC entry 3577 (class 0 OID 0)
-- Dependencies: 238
-- Name: produk_id_produk_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.produk_id_produk_seq OWNED BY public.produk.id_produk;


--
-- TOC entry 239 (class 1259 OID 35136)
-- Name: progress_id_progress_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.progress_id_progress_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.progress_id_progress_seq OWNER TO postgres;

--
-- TOC entry 3578 (class 0 OID 0)
-- Dependencies: 239
-- Name: progress_id_progress_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.progress_id_progress_seq OWNED BY public.penelitian.id_penelitian;


--
-- TOC entry 245 (class 1259 OID 35288)
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
-- TOC entry 246 (class 1259 OID 35293)
-- Name: ruang_lab_id_ruang_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ruang_lab_id_ruang_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ruang_lab_id_ruang_seq OWNER TO postgres;

--
-- TOC entry 3579 (class 0 OID 0)
-- Dependencies: 246
-- Name: ruang_lab_id_ruang_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ruang_lab_id_ruang_seq OWNED BY public.ruang_lab.id_ruang_lab;


--
-- TOC entry 240 (class 1259 OID 35137)
-- Name: settings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.settings (
    id integer NOT NULL,
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
    updated_by integer
);


ALTER TABLE public.settings OWNER TO postgres;

--
-- TOC entry 250 (class 1259 OID 35359)
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
    pj.created_at
   FROM (public.peminjaman pj
     JOIN public.alat_lab alat ON ((alat.id_alat = pj.id_alat)))
  WHERE ((pj.status)::text = 'dipinjam'::text);


ALTER TABLE public.view_alat_dipinjam OWNER TO postgres;

--
-- TOC entry 247 (class 1259 OID 35334)
-- Name: view_alat_tersedia; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.view_alat_tersedia AS
 SELECT alat.id_alat,
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
          GROUP BY peminjaman.id_alat) pj ON ((pj.id_alat = alat.id_alat)));


ALTER TABLE public.view_alat_tersedia OWNER TO postgres;

--
-- TOC entry 241 (class 1259 OID 35144)
-- Name: visitor; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.visitor (
    id_visitor integer NOT NULL,
    id_pengunjung integer NOT NULL,
    visit_count integer DEFAULT 0 NOT NULL,
    last_visit timestamp with time zone,
    first_visit timestamp with time zone DEFAULT now(),
    keterangan character varying(500)
);


ALTER TABLE public.visitor OWNER TO postgres;

--
-- TOC entry 242 (class 1259 OID 35151)
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
-- TOC entry 3580 (class 0 OID 0)
-- Dependencies: 242
-- Name: visitor_id_visitor_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.visitor_id_visitor_seq OWNED BY public.visitor.id_visitor;


--
-- TOC entry 3267 (class 2604 OID 35318)
-- Name: absensi id_absensi; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.absensi ALTER COLUMN id_absensi SET DEFAULT nextval('public.absensi_id_absensi_seq'::regclass);


--
-- TOC entry 3269 (class 2604 OID 35319)
-- Name: admin id_admin; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin ALTER COLUMN id_admin SET DEFAULT nextval('public.admin_id_admin_seq'::regclass);


--
-- TOC entry 3272 (class 2604 OID 35154)
-- Name: alat_lab id_alat; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.alat_lab ALTER COLUMN id_alat SET DEFAULT nextval('public.alat_lab_id_alat_seq'::regclass);


--
-- TOC entry 3276 (class 2604 OID 35320)
-- Name: artikel id_artikel; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.artikel ALTER COLUMN id_artikel SET DEFAULT nextval('public.artikel_id_artikel_seq'::regclass);


--
-- TOC entry 3277 (class 2604 OID 35321)
-- Name: berita id_berita; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.berita ALTER COLUMN id_berita SET DEFAULT nextval('public.berita_id_berita_seq'::regclass);


--
-- TOC entry 3306 (class 2604 OID 35346)
-- Name: buku_tamu id_buku_tamu; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.buku_tamu ALTER COLUMN id_buku_tamu SET DEFAULT nextval('public.buku_tamu_id_buku_tamu_seq'::regclass);


--
-- TOC entry 3302 (class 2604 OID 35322)
-- Name: fokus_penelitian id_fp; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fokus_penelitian ALTER COLUMN id_fp SET DEFAULT nextval('public.fokus_penelitian_id_fr_seq'::regclass);


--
-- TOC entry 3279 (class 2604 OID 35323)
-- Name: gallery id_gallery; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gallery ALTER COLUMN id_gallery SET DEFAULT nextval('public.gallery_id_gallery_seq'::regclass);


--
-- TOC entry 3283 (class 2604 OID 35158)
-- Name: mahasiswa id_mhs; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mahasiswa ALTER COLUMN id_mhs SET DEFAULT nextval('public.mahasiswa_id_mhs_seq'::regclass);


--
-- TOC entry 3285 (class 2604 OID 35324)
-- Name: member id_member; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.member ALTER COLUMN id_member SET DEFAULT nextval('public.member_id_member_seq'::regclass);


--
-- TOC entry 3286 (class 2604 OID 35325)
-- Name: mitra id_mitra; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mitra ALTER COLUMN id_mitra SET DEFAULT nextval('public.mitra_id_mitra_seq'::regclass);


--
-- TOC entry 3287 (class 2604 OID 35326)
-- Name: peminjaman id_peminjaman; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.peminjaman ALTER COLUMN id_peminjaman SET DEFAULT nextval('public.peminjaman_id_peminjaman_seq'::regclass);


--
-- TOC entry 3290 (class 2604 OID 35327)
-- Name: penelitian id_penelitian; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian ALTER COLUMN id_penelitian SET DEFAULT nextval('public.progress_id_progress_seq'::regclass);


--
-- TOC entry 3293 (class 2604 OID 35328)
-- Name: pengunjung id_pengunjung; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pengunjung ALTER COLUMN id_pengunjung SET DEFAULT nextval('public.pengunjung_id_pengunjung_seq'::regclass);


--
-- TOC entry 3296 (class 2604 OID 35329)
-- Name: produk id_produk; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.produk ALTER COLUMN id_produk SET DEFAULT nextval('public.produk_id_produk_seq'::regclass);


--
-- TOC entry 3303 (class 2604 OID 35330)
-- Name: ruang_lab id_ruang_lab; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ruang_lab ALTER COLUMN id_ruang_lab SET DEFAULT nextval('public.ruang_lab_id_ruang_seq'::regclass);


--
-- TOC entry 3299 (class 2604 OID 35331)
-- Name: visitor id_visitor; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visitor ALTER COLUMN id_visitor SET DEFAULT nextval('public.visitor_id_visitor_seq'::regclass);


--
-- TOC entry 3524 (class 0 OID 35045)
-- Dependencies: 214
-- Data for Name: absensi; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.absensi (id_absensi, id_mhs, waktu_datang, waktu_pulang, keterangan, tanggal) FROM stdin;
1	2	2025-11-24 19:08:16.860885	2025-11-24 19:14:08.243709	rtery	2025-11-24
\.


--
-- TOC entry 3526 (class 0 OID 35052)
-- Dependencies: 216
-- Data for Name: admin; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.admin (id_admin, username, password_hash, role, created_at) FROM stdin;
1	admin	$2y$10$IdJbZzxJMmF45uzjjB88SuCAfGS15ftjoiJtGCpI.PTsG2W626ORW	admin	2025-11-10 11:48:49.613148+07
2	daniel	$2y$10$zEtWiarTXTq0y5yaVYYnEeijnEev5Y.hp/NNywUQLCK9L.VYaKJzm	non_aktif	2025-11-10 12:05:37.129494+07
\.


--
-- TOC entry 3528 (class 0 OID 35058)
-- Dependencies: 218
-- Data for Name: alat_lab; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.alat_lab (id_alat, nama_alat, deskripsi, stock, created_at, updated_at, id_admin) FROM stdin;
\.


--
-- TOC entry 3530 (class 0 OID 35067)
-- Dependencies: 220
-- Data for Name: artikel; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.artikel (id_artikel, judul, tahun, konten) FROM stdin;
5	fjabf	2023	dfjhfv,ja
8	fjabs,	2022	vjshbd,js
10	fjdshvb,j	2023	fjahrbf,ja
12	fvwwjfj	2021	jhdbsh,as
\.


--
-- TOC entry 3532 (class 0 OID 35073)
-- Dependencies: 222
-- Data for Name: berita; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.berita (id_berita, judul, konten, gambar_thumbnail, created_at, id_admin) FROM stdin;
\.


--
-- TOC entry 3558 (class 0 OID 35343)
-- Dependencies: 249
-- Data for Name: buku_tamu; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.buku_tamu (id_buku_tamu, nama, email, institusi, no_hp, pesan, created_at, is_read, admin_response) FROM stdin;
\.


--
-- TOC entry 3553 (class 0 OID 35282)
-- Dependencies: 243
-- Data for Name: fokus_penelitian; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.fokus_penelitian (id_fp, title, deskripsi, detail) FROM stdin;
\.


--
-- TOC entry 3534 (class 0 OID 35080)
-- Dependencies: 224
-- Data for Name: gallery; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.gallery (id_gallery, id_berita, gambar, judul, deskripsi, urutan, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 3536 (class 0 OID 35089)
-- Dependencies: 226
-- Data for Name: mahasiswa; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.mahasiswa (id_mhs, nama, title, tahun, status, id_admin) FROM stdin;
2	ell	\N	2024	magang	1
\.


--
-- TOC entry 3538 (class 0 OID 35095)
-- Dependencies: 228
-- Data for Name: member; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.member (id_member, nama, email, jabatan, foto, bidang_keahlian, notlp, deskripsi, alamat, id_admin) FROM stdin;
6	Fata Haidar Aly	fata@gmail.com	penunggu	\N	\N	081259818891	hayamuruk	Perum. Putri Juanda Blok A2/ No.3, Desa Pepe, Kec. Sedati, Kab. Sidoarjo, Prov.Jawa Timur	\N
\.


--
-- TOC entry 3540 (class 0 OID 35101)
-- Dependencies: 230
-- Data for Name: mitra; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.mitra (id_mitra, nama_institusi, logo) FROM stdin;
\.


--
-- TOC entry 3542 (class 0 OID 35107)
-- Dependencies: 232
-- Data for Name: peminjaman; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.peminjaman (id_peminjaman, id_alat, nama_peminjam, tanggal_pinjam, tanggal_kembali, status, keterangan, created_at) FROM stdin;
\.


--
-- TOC entry 3544 (class 0 OID 35115)
-- Dependencies: 234
-- Data for Name: penelitian; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.penelitian (id_penelitian, id_artikel, id_mhs, judul, tahun, id_member, deskripsi, created_at, id_produk, id_mitra, tgl_mulai, tgl_selesai) FROM stdin;
\.


--
-- TOC entry 3545 (class 0 OID 35122)
-- Dependencies: 235
-- Data for Name: pengunjung; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pengunjung (id_pengunjung, nama, email, institusi, no_hp, pesan, created_at, is_read, admin_response) FROM stdin;
1	muhammad daniel	daniel@gmail.com	polinema	\N	\N	2025-11-10 12:05:37.136092+07	f	\N
\.


--
-- TOC entry 3547 (class 0 OID 35130)
-- Dependencies: 237
-- Data for Name: produk; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.produk (id_produk, nama_produk, deskripsi) FROM stdin;
\.


--
-- TOC entry 3555 (class 0 OID 35288)
-- Dependencies: 245
-- Data for Name: ruang_lab; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ruang_lab (id_ruang_lab, nama_ruang, status, id_admin, created_at) FROM stdin;
\.


--
-- TOC entry 3550 (class 0 OID 35137)
-- Dependencies: 240
-- Data for Name: settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.settings (id, site_title, site_subtitle, site_logo, created_at, updated_at, footer_logo, footer_title, copyright_text, contact_email, contact_phone, contact_address, updated_by) FROM stdin;
\.


--
-- TOC entry 3551 (class 0 OID 35144)
-- Dependencies: 241
-- Data for Name: visitor; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.visitor (id_visitor, id_pengunjung, visit_count, last_visit, first_visit, keterangan) FROM stdin;
1	1	1	2025-11-10 12:05:52.742793+07	2025-11-10 12:05:37.144199+07	\N
\.


--
-- TOC entry 3581 (class 0 OID 0)
-- Dependencies: 215
-- Name: absensi_id_absensi_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.absensi_id_absensi_seq', 1, true);


--
-- TOC entry 3582 (class 0 OID 0)
-- Dependencies: 217
-- Name: admin_id_admin_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.admin_id_admin_seq', 2, true);


--
-- TOC entry 3583 (class 0 OID 0)
-- Dependencies: 219
-- Name: alat_lab_id_alat_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.alat_lab_id_alat_seq', 1, true);


--
-- TOC entry 3584 (class 0 OID 0)
-- Dependencies: 221
-- Name: artikel_id_artikel_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.artikel_id_artikel_seq', 13, true);


--
-- TOC entry 3585 (class 0 OID 0)
-- Dependencies: 223
-- Name: berita_id_berita_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.berita_id_berita_seq', 6, true);


--
-- TOC entry 3586 (class 0 OID 0)
-- Dependencies: 248
-- Name: buku_tamu_id_buku_tamu_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.buku_tamu_id_buku_tamu_seq', 1, false);


--
-- TOC entry 3587 (class 0 OID 0)
-- Dependencies: 244
-- Name: fokus_penelitian_id_fr_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.fokus_penelitian_id_fr_seq', 1, false);


--
-- TOC entry 3588 (class 0 OID 0)
-- Dependencies: 225
-- Name: gallery_id_gallery_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.gallery_id_gallery_seq', 4, true);


--
-- TOC entry 3589 (class 0 OID 0)
-- Dependencies: 227
-- Name: mahasiswa_id_mhs_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.mahasiswa_id_mhs_seq', 2, true);


--
-- TOC entry 3590 (class 0 OID 0)
-- Dependencies: 229
-- Name: member_id_member_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.member_id_member_seq', 5, true);


--
-- TOC entry 3591 (class 0 OID 0)
-- Dependencies: 231
-- Name: mitra_id_mitra_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.mitra_id_mitra_seq', 1, true);


--
-- TOC entry 3592 (class 0 OID 0)
-- Dependencies: 233
-- Name: peminjaman_id_peminjaman_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.peminjaman_id_peminjaman_seq', 1, false);


--
-- TOC entry 3593 (class 0 OID 0)
-- Dependencies: 236
-- Name: pengunjung_id_pengunjung_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pengunjung_id_pengunjung_seq', 1, true);


--
-- TOC entry 3594 (class 0 OID 0)
-- Dependencies: 238
-- Name: produk_id_produk_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.produk_id_produk_seq', 1, false);


--
-- TOC entry 3595 (class 0 OID 0)
-- Dependencies: 239
-- Name: progress_id_progress_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.progress_id_progress_seq', 1, false);


--
-- TOC entry 3596 (class 0 OID 0)
-- Dependencies: 246
-- Name: ruang_lab_id_ruang_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ruang_lab_id_ruang_seq', 1, false);


--
-- TOC entry 3597 (class 0 OID 0)
-- Dependencies: 242
-- Name: visitor_id_visitor_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.visitor_id_visitor_seq', 1, true);


--
-- TOC entry 3311 (class 2606 OID 35167)
-- Name: absensi absensi_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.absensi
    ADD CONSTRAINT absensi_pkey PRIMARY KEY (id_absensi);


--
-- TOC entry 3317 (class 2606 OID 35169)
-- Name: alat_lab alat_lab_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.alat_lab
    ADD CONSTRAINT alat_lab_pkey PRIMARY KEY (id_alat);


--
-- TOC entry 3320 (class 2606 OID 35171)
-- Name: artikel artikel_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.artikel
    ADD CONSTRAINT artikel_pkey PRIMARY KEY (id_artikel);


--
-- TOC entry 3322 (class 2606 OID 35173)
-- Name: berita berita_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.berita
    ADD CONSTRAINT berita_pkey PRIMARY KEY (id_berita);


--
-- TOC entry 3361 (class 2606 OID 35352)
-- Name: buku_tamu buku_tamu_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.buku_tamu
    ADD CONSTRAINT buku_tamu_pkey PRIMARY KEY (id_buku_tamu);


--
-- TOC entry 3356 (class 2606 OID 35309)
-- Name: fokus_penelitian fokus_penelitian_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fokus_penelitian
    ADD CONSTRAINT fokus_penelitian_pkey PRIMARY KEY (id_fp);


--
-- TOC entry 3325 (class 2606 OID 35175)
-- Name: gallery gallery_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gallery
    ADD CONSTRAINT gallery_pkey PRIMARY KEY (id_gallery);


--
-- TOC entry 3313 (class 2606 OID 35177)
-- Name: admin id_admin_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin
    ADD CONSTRAINT id_admin_pkey PRIMARY KEY (id_admin);


--
-- TOC entry 3331 (class 2606 OID 35179)
-- Name: mahasiswa mahasiswa_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mahasiswa
    ADD CONSTRAINT mahasiswa_pkey PRIMARY KEY (id_mhs);


--
-- TOC entry 3334 (class 2606 OID 35181)
-- Name: member member_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.member
    ADD CONSTRAINT member_pkey PRIMARY KEY (id_member);


--
-- TOC entry 3336 (class 2606 OID 35183)
-- Name: mitra mitra_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mitra
    ADD CONSTRAINT mitra_pkey PRIMARY KEY (id_mitra);


--
-- TOC entry 3339 (class 2606 OID 35185)
-- Name: peminjaman peminjaman_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.peminjaman
    ADD CONSTRAINT peminjaman_pkey PRIMARY KEY (id_peminjaman);


--
-- TOC entry 3344 (class 2606 OID 35187)
-- Name: penelitian penelitian_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT penelitian_pkey PRIMARY KEY (id_penelitian);


--
-- TOC entry 3346 (class 2606 OID 35189)
-- Name: pengunjung pengunjung_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pengunjung
    ADD CONSTRAINT pengunjung_pkey PRIMARY KEY (id_pengunjung);


--
-- TOC entry 3349 (class 2606 OID 35191)
-- Name: produk produk_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.produk
    ADD CONSTRAINT produk_pkey PRIMARY KEY (id_produk);


--
-- TOC entry 3359 (class 2606 OID 35311)
-- Name: ruang_lab ruang_lab_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ruang_lab
    ADD CONSTRAINT ruang_lab_pkey PRIMARY KEY (id_ruang_lab);


--
-- TOC entry 3351 (class 2606 OID 35193)
-- Name: settings settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (id);


--
-- TOC entry 3315 (class 2606 OID 35195)
-- Name: admin users_username_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- TOC entry 3354 (class 2606 OID 35197)
-- Name: visitor visitor_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visitor
    ADD CONSTRAINT visitor_pkey PRIMARY KEY (id_visitor);


--
-- TOC entry 3318 (class 1259 OID 35198)
-- Name: idx_alatlab_id_admin; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_alatlab_id_admin ON public.alat_lab USING btree (id_admin);


--
-- TOC entry 3323 (class 1259 OID 35199)
-- Name: idx_berita_id_admin; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_berita_id_admin ON public.berita USING btree (id_admin);


--
-- TOC entry 3362 (class 1259 OID 35353)
-- Name: idx_buku_tamu_created_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_buku_tamu_created_at ON public.buku_tamu USING btree (created_at DESC);


--
-- TOC entry 3363 (class 1259 OID 35355)
-- Name: idx_buku_tamu_email; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_buku_tamu_email ON public.buku_tamu USING btree (email);


--
-- TOC entry 3364 (class 1259 OID 35354)
-- Name: idx_buku_tamu_is_read; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_buku_tamu_is_read ON public.buku_tamu USING btree (is_read);


--
-- TOC entry 3326 (class 1259 OID 35200)
-- Name: idx_gallery_created; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_gallery_created ON public.gallery USING btree (created_at);


--
-- TOC entry 3327 (class 1259 OID 35201)
-- Name: idx_gallery_id_berita; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_gallery_id_berita ON public.gallery USING btree (id_berita);


--
-- TOC entry 3328 (class 1259 OID 35202)
-- Name: idx_gallery_urutan; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_gallery_urutan ON public.gallery USING btree (urutan);


--
-- TOC entry 3329 (class 1259 OID 35203)
-- Name: idx_mahasiswa_id_admin; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_mahasiswa_id_admin ON public.mahasiswa USING btree (id_admin);


--
-- TOC entry 3332 (class 1259 OID 35204)
-- Name: idx_member_id_admin; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_member_id_admin ON public.member USING btree (id_admin);


--
-- TOC entry 3337 (class 1259 OID 35205)
-- Name: idx_peminjaman_id_alat; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_peminjaman_id_alat ON public.peminjaman USING btree (id_alat);


--
-- TOC entry 3347 (class 1259 OID 35206)
-- Name: idx_produk_nama; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_produk_nama ON public.produk USING btree (nama_produk);


--
-- TOC entry 3340 (class 1259 OID 35207)
-- Name: idx_progress_artikel; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_progress_artikel ON public.penelitian USING btree (id_artikel);


--
-- TOC entry 3341 (class 1259 OID 35208)
-- Name: idx_progress_member; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_progress_member ON public.penelitian USING btree (id_member);


--
-- TOC entry 3342 (class 1259 OID 35209)
-- Name: idx_progress_mhs; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_progress_mhs ON public.penelitian USING btree (id_mhs);


--
-- TOC entry 3357 (class 1259 OID 35312)
-- Name: idx_ruanglab_nama; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_ruanglab_nama ON public.ruang_lab USING btree (nama_ruang);


--
-- TOC entry 3352 (class 1259 OID 35210)
-- Name: idx_visitor_pengunjung; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_visitor_pengunjung ON public.visitor USING btree (id_pengunjung);


--
-- TOC entry 3365 (class 2606 OID 35211)
-- Name: absensi fk_absensi_mahasiswa; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.absensi
    ADD CONSTRAINT fk_absensi_mahasiswa FOREIGN KEY (id_mhs) REFERENCES public.mahasiswa(id_mhs) ON DELETE RESTRICT;


--
-- TOC entry 3366 (class 2606 OID 35216)
-- Name: alat_lab fk_alatlab_admin; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.alat_lab
    ADD CONSTRAINT fk_alatlab_admin FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3367 (class 2606 OID 35221)
-- Name: berita fk_berita_admin; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.berita
    ADD CONSTRAINT fk_berita_admin FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON DELETE SET NULL;


--
-- TOC entry 3368 (class 2606 OID 35226)
-- Name: gallery fk_gallery_berita; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gallery
    ADD CONSTRAINT fk_gallery_berita FOREIGN KEY (id_berita) REFERENCES public.berita(id_berita) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 3369 (class 2606 OID 35231)
-- Name: mahasiswa fk_mahasiswa_admin; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mahasiswa
    ADD CONSTRAINT fk_mahasiswa_admin FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3370 (class 2606 OID 35236)
-- Name: member fk_member_admin; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.member
    ADD CONSTRAINT fk_member_admin FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3371 (class 2606 OID 35241)
-- Name: peminjaman fk_peminjaman_alat; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.peminjaman
    ADD CONSTRAINT fk_peminjaman_alat FOREIGN KEY (id_alat) REFERENCES public.alat_lab(id_alat) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3372 (class 2606 OID 35246)
-- Name: penelitian fk_penelitian_mitra; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT fk_penelitian_mitra FOREIGN KEY (id_mitra) REFERENCES public.mitra(id_mitra) ON DELETE SET NULL;


--
-- TOC entry 3373 (class 2606 OID 35251)
-- Name: penelitian fk_penelitian_produk; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT fk_penelitian_produk FOREIGN KEY (id_produk) REFERENCES public.produk(id_produk) ON DELETE SET NULL;


--
-- TOC entry 3379 (class 2606 OID 35313)
-- Name: ruang_lab fk_ruang_admin; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ruang_lab
    ADD CONSTRAINT fk_ruang_admin FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 3377 (class 2606 OID 35256)
-- Name: settings fk_settings_admin; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT fk_settings_admin FOREIGN KEY (updated_by) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3374 (class 2606 OID 35261)
-- Name: penelitian progress_id_artikel_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT progress_id_artikel_fkey FOREIGN KEY (id_artikel) REFERENCES public.artikel(id_artikel) ON DELETE SET NULL;


--
-- TOC entry 3375 (class 2606 OID 35266)
-- Name: penelitian progress_id_member_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT progress_id_member_fkey FOREIGN KEY (id_member) REFERENCES public.member(id_member) ON DELETE SET NULL;


--
-- TOC entry 3376 (class 2606 OID 35271)
-- Name: penelitian progress_id_mhs_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.penelitian
    ADD CONSTRAINT progress_id_mhs_fkey FOREIGN KEY (id_mhs) REFERENCES public.mahasiswa(id_mhs) ON DELETE SET NULL;


--
-- TOC entry 3378 (class 2606 OID 35276)
-- Name: visitor visitor_id_pengunjung_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visitor
    ADD CONSTRAINT visitor_id_pengunjung_fkey FOREIGN KEY (id_pengunjung) REFERENCES public.pengunjung(id_pengunjung) ON DELETE CASCADE;


-- Completed on 2025-11-27 10:31:04

--
-- PostgreSQL database dump complete
--

\unrestrict KK6d3eiVNOHlF8FXisPNWk0Zy2fkjz3UnFUJbDOPaBnhsAGbjfOtIKBbK7JTyOZ

