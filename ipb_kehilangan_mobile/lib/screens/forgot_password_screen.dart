import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../services/auth_service.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final emailController = TextEditingController();
  final otpController = TextEditingController();
  final passwordController = TextEditingController();
  final confirmPasswordController = TextEditingController();

  bool otpSent = false;
  bool isLoading = false;
  bool _obscurePassword = true;

  Future<void> sendOtp() async {
    final email = emailController.text.trim().toLowerCase();

    if (email.isEmpty) {
      _showMessage("Email wajib diisi");
      return;
    }

    setState(() => isLoading = true);

    try {
      await AuthService.forgotPassword(email);
      setState(() => otpSent = true);
      _showMessage("Kode OTP reset password sudah dikirim");
    } catch (e) {
      _showMessage(e.toString().replaceFirst('Exception: ', ''));
    }

    if (mounted) {
      setState(() => isLoading = false);
    }
  }

  Future<void> resetPassword() async {
    final email = emailController.text.trim().toLowerCase();
    final otp = otpController.text.trim();
    final password = passwordController.text;
    final confirmation = confirmPasswordController.text;

    if (otp.length != 6 || password.isEmpty || confirmation.isEmpty) {
      _showMessage("OTP dan password baru wajib diisi");
      return;
    }

    setState(() => isLoading = true);

    try {
      await AuthService.resetPassword(email, otp, password, confirmation);
      if (!mounted) return;
      _showMessage("Password berhasil diubah. Silakan login.");
      Navigator.pop(context);
    } catch (e) {
      _showMessage(e.toString().replaceFirst('Exception: ', ''));
    }

    if (mounted) {
      setState(() => isLoading = false);
    }
  }

  void _showMessage(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFEDEDED),
      appBar: AppBar(
        title: const Text("Reset Password"),
        backgroundColor: const Color(0xFFEDEDED),
        foregroundColor: Colors.black,
        elevation: 0,
      ),
      body: Padding(
        padding: const EdgeInsets.all(25),
        child: SingleChildScrollView(
          child: Column(
            children: [
              const SizedBox(height: 30),
              TextField(
                controller: emailController,
                keyboardType: TextInputType.emailAddress,
                inputFormatters: [_LowerCaseTextFormatter()],
                enabled: !otpSent,
                decoration: _inputDecoration("Email terdaftar"),
              ),
              if (otpSent) ...[
                const SizedBox(height: 15),
                TextField(
                  controller: otpController,
                  keyboardType: TextInputType.number,
                  maxLength: 6,
                  inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                  decoration: _inputDecoration("Kode OTP").copyWith(
                    counterText: "",
                  ),
                ),
                const SizedBox(height: 15),
                TextField(
                  controller: passwordController,
                  obscureText: _obscurePassword,
                  decoration: _inputDecoration("Password baru").copyWith(
                    suffixIcon: _passwordToggle(),
                  ),
                ),
                const SizedBox(height: 15),
                TextField(
                  controller: confirmPasswordController,
                  obscureText: _obscurePassword,
                  decoration: _inputDecoration("Konfirmasi password baru"),
                ),
              ],
              const SizedBox(height: 25),
              ElevatedButton(
                onPressed: isLoading ? null : (otpSent ? resetPassword : sendOtp),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF3B4CCA),
                  foregroundColor: Colors.white,
                  minimumSize: const Size(double.infinity, 50),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(25),
                  ),
                ),
                child: isLoading
                    ? const CircularProgressIndicator(color: Colors.white)
                    : Text(otpSent ? "Simpan Password Baru" : "Kirim OTP"),
              ),
            ],
          ),
        ),
      ),
    );
  }

  InputDecoration _inputDecoration(String hint) {
    return InputDecoration(
      hintText: hint,
      filled: true,
      fillColor: Colors.white,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
      ),
    );
  }

  Widget _passwordToggle() {
    return IconButton(
      icon: Icon(
        _obscurePassword ? Icons.visibility_off : Icons.visibility,
        color: Colors.grey,
      ),
      onPressed: () {
        setState(() {
          _obscurePassword = !_obscurePassword;
        });
      },
    );
  }
}

class _LowerCaseTextFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(
    TextEditingValue oldValue,
    TextEditingValue newValue,
  ) {
    return newValue.copyWith(text: newValue.text.toLowerCase());
  }
}
