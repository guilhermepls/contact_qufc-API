from flask import Flask, request, jsonify
import smtplib
import os
from dotenv import load_dotenv
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText

# Carregar variáveis de ambiente do arquivo .env
load_dotenv()

app = Flask(__name__)

@app.route('/api/send', methods=['POST'])
def send_email():
    data = request.get_json()
    nome = data['nome']
    email = data['email']
    mensagem = data['mensagem']
    
    para = 'equipequfc@gmail.com'
    assunto = 'Fale Conosco - Equipe QUFC'

    msg = MIMEMultipart()
    msg['From'] = email
    msg['To'] = para
    msg['Subject'] = assunto

    body = f"Nome: {nome}\nEmail: {email}\n\nMensagem:\n{mensagem}"
    msg.attach(MIMEText(body, 'plain'))

    try:
        servidor = smtplib.SMTP('smtp.gmail.com', 587)
        servidor.starttls()
        password = os.getenv('password')
        servidor.login(email, password)
        servidor.sendmail(email, para, msg.as_string())
        servidor.quit()
        return jsonify({'message': 'Email enviado com sucesso!'}), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True)
