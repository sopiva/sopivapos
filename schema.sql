--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;

--
-- Name: plpgsql; Type: PROCEDURAL LANGUAGE; Schema: -; Owner: -
--

CREATE PROCEDURAL LANGUAGE plpgsql;

--
-- Name: create_product(integer, text, integer); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION create_product(integer, text, integer)
RETURNS integer LANGUAGE sql AS $_$
    INSERT INTO products ("code", "brand_id", "name", "price")
        VALUES (products_newid($1), $1, $2, $3)
        RETURNING id;
$_$;


--
-- Name: products_newid(integer); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION products_newid(integer)
RETURNS integer LANGUAGE plpgsql AS $_$
DECLARE
  bid integer := $1;
  rid integer := 0;
  mid integer := 0;
BEGIN
    FOR rid IN SELECT code % 1000 FROM products WHERE brand_id = bid ORDER BY code ASC
    LOOP
        EXIT WHEN (rid <> mid AND rid <> mid+1);
        mid := rid;
    END LOOP;
    IF (mid < 999) THEN
        rid := bid * 1000 + mid + 1;
    ELSE
        SELECT COALESCE(MAX(id),0)+1 INTO mid FROM brands;
        SELECT COALESCE(MAX(id),0)+1 INTO rid FROM products;
        IF (rid < (mid * 1000 + 1)) THEN
            rid := mid * 1000 + 1;
        END IF;
    END IF;
    RETURN rid;
END;
$_$;

--
-- Name: brands; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE brands (
    id serial primary key,
    abbr text NOT NULL,
    name text NOT NULL,
    inactive integer DEFAULT 0 NOT NULL
);


--
-- Name: cake_sessions; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE cake_sessions (
    id character varying(255) DEFAULT ''::character varying NOT NULL,
    data text,
    expires integer
);


--
-- Name: options; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE options (
    id serial primary key,
    key text,
    val text
);


--
-- Name: products; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE products (
    id serial primary key,
    code integer NOT NULL unique,
    brand_id integer NOT NULL REFERENCES brands(id) ON UPDATE CASCADE ON DELETE CASCADE,
    name text NOT NULL,
    price integer NOT NULL
);



--
-- Name: receipt_items; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE receipt_items (
    id serial primary key,
    receipt_id integer NOT NULL REFERENCES receipts(id) ON UPDATE CASCADE ON DELETE CASCADE,
    cnt integer DEFAULT 1 NOT NULL,
    sum integer DEFAULT 0 NOT NULL,
    vat integer DEFAULT 22 NOT NULL,
    name text,
    brand_id integer REFERENCES brands(id) ON UPDATE CASCADE ON DELETE SET NULL
);


--
-- Name: receipt_payments; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE receipt_payments (
    id serial primary key,
    receipt_id integer NOT NULL REFERENCES receipts(id) ON UPDATE CASCADE ON DELETE CASCADE,
    sum integer DEFAULT 0 NOT NULL,
    name text
);


--
-- Name: receipts; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE receipts (
    id serial primary key,
    "time" timestamp with time zone DEFAULT now() NOT NULL,
    day_id integer DEFAULT 0 NOT NULL,
    person text NOT NULL,
    number integer
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -; Tablespace:
--

CREATE TABLE users (
    user_id serial primary key,
    username text NOT NULL,
    password text NOT NULL,
    inactive integer DEFAULT 0 NOT NULL,
    admin integer DEFAULT 0 NOT NULL
);
