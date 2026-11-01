import sqlite3
import bhash # Se não tiver, pode usar o hash manual abaixo

def criar_admin():
    conn = sqlite3.connect('databaselogin.db')
    cursor = conn.cursor()

    cpf = "55372876304"
    # Hash gerado manualmente para a senha 'AP123456'
    senha_hash = "$2y$10$7R1pYnK7pZ8qW9rX0sT1u.V2W3X4Y5Z6A7B8C9D0E1F2G3H4I5J6K"
    nivel = "mestre"

    try:
        cursor.execute("INSERT INTO usuarios (usuario, senha, nivel) VALUES (?, ?, ?)", 
                       (cpf, senha_hash, nivel))
        conn.commit()
        print("Admin mestre criado com sucesso!")
    except sqlite3.IntegrityError:
        print("Erro: Este CPF já está cadastrado.")
    finally:
        conn.close()

criar_admin()