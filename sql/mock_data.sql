-- TODO --
USE maturita;

INSERT INTO users (id, username, password)
VALUES 
(1, "example", md5("12345")),
(2, "second", md5("12345"));


INSERT INTO study_sets (id, user_id, title, description)
VALUES
(1, 1, "Test", "testovaci balicek"),
(2, 1, "Test 2", "dalsi balicek"),
(3, 2, "Test 3", "testovaci balicek druheho uzivatele");


INSERT INTO cards (id, study_set_id, front_text, back_text)
VALUES
(1, 1, "Hallo", "Ahoj"),
(2, 1, "sprechen", "mluvit"),
(3, 2, "lesen", "číst"),
(4, 1, "das Essen", "jídlo"),
(5, 1, "laufen", "běhat"),
(6, 3, "hello", "ahoj"),
(7, 3, "dog", "pes"),
(8, 3, "cat", "kočka"),
(9, 1, "langsam", "pomalý"),
(10, 1, "die Währung", "měna"),
(11, 1, "die Hauptstadt", "hlavní město"),
(12, 1, "durch", "skrz"),
(13, 1, "gehen", "jít"),
(14, 1, "haben", "mít"),
(15, 1, "morgen", "zítra"),
(16, 1, "teuer", "drahý"),
(17, 1, "billig", "levný");
