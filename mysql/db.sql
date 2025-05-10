CREATE TABLE past_papers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year VARCHAR(10),
    course VARCHAR(100),
    title VARCHAR(255),
    code VARCHAR(50),
    folder_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE paper_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paper_id INT,
    filename VARCHAR(255),
    FOREIGN KEY (paper_id) REFERENCES past_papers(id) ON DELETE CASCADE
);
