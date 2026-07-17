package koneksi;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import javax.swing.JOptionPane;

public class KoneksiDB {

    private static Connection conn = null;
    private static final String DB_URL = "jdbc:mysql://localhost:3306/absenv2";
    private static final String DB_USER = "root";
    private static final String DB_PASS = "";

    public static Connection getKoneksi() {
        try {
            if (conn == null || conn.isClosed()) {
                Class.forName("com.mysql.cj.jdbc.Driver");
                conn = DriverManager.getConnection(DB_URL, DB_USER, DB_PASS);
            }
        } catch (ClassNotFoundException e) {
            JOptionPane.showMessageDialog(null,
                    "Driver JDBC MySQL tidak ditemukan!\n" + e.getMessage(),
                    "Koneksi DB Gagal", JOptionPane.ERROR_MESSAGE);
        } catch (SQLException e) {
            JOptionPane.showMessageDialog(null,
                    "Gagal koneksi ke database!\n" + e.getMessage(),
                    "Koneksi DB Gagal", JOptionPane.ERROR_MESSAGE);
        }
        return conn;
    }

    public static void closeKoneksi() {
        try {
            if (conn != null && !conn.isClosed()) {
                conn.close();
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
}
