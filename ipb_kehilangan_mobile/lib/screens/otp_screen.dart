import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../services/auth_service.dart';
import 'main_screen.dart';

class OtpScreen extends StatefulWidget {
  const OtpScreen({
    super.key,
    required this.email,
    required this.purpose,
    required this.registrationToken,
  });

  final String email;
  final String purpose;
  final String registrationToken;

  @override
  State<OtpScreen> createState() => _OtpScreenState();
}

class _OtpScreenState extends State<OtpScreen> {
  final otpController = TextEditingController();
  bool isLoading = false;
  bool isResending = false;

  Future<void> verify() async {
    final otp = otpController.text.trim();

    if (otp.length != 6) {
      _showMessage("Masukkan 6 digit OTP");
      return;
    }

    setState(() => isLoading = true);

    try {
      final token = await AuthService.verifyOtp(
        widget.email,
        otp,
        widget.purpose,
        widget.registrationToken,
      );
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('token', token);

      if (!mounted) return;
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(builder: (_) => const MainScreen()),
        (_) => false,
      );
    } catch (e) {
      _showMessage(e.toString().replaceFirst('Exception: ', ''));
    }

    if (mounted) {
      setState(() => isLoading = false);
    }
  }

  Future<void> resend() async {
    setState(() => isResending = true);

    try {
      final newToken = await AuthService.resendOtp(
        widget.email,
        widget.purpose,
        registrationToken: widget.registrationToken,
      );
      if (newToken != null && mounted) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (_) => OtpScreen(
              email: widget.email,
              purpose: widget.purpose,
              registrationToken: newToken,
            ),
          ),
        );
        return;
      }
      _showMessage("Kode OTP baru sudah dikirim");
    } catch (e) {
      _showMessage(e.toString().replaceFirst('Exception: ', ''));
    }

    if (mounted) {
      setState(() => isResending = false);
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
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 25),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Text(
                "Verifikasi OTP",
                style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 12),
              Text(
                "Masukkan kode 6 digit yang dikirim ke\n${widget.email}",
                textAlign: TextAlign.center,
                style: const TextStyle(color: Colors.black54),
              ),
              const SizedBox(height: 30),
              TextField(
                controller: otpController,
                keyboardType: TextInputType.number,
                maxLength: 6,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 24, letterSpacing: 8),
                inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                decoration: InputDecoration(
                  counterText: "",
                  hintText: "000000",
                  filled: true,
                  fillColor: Colors.white,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: isLoading ? null : verify,
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
                    : const Text("Verifikasi & Masuk"),
              ),
              const SizedBox(height: 12),
              TextButton(
                onPressed: isResending ? null : resend,
                child: Text(isResending ? "Mengirim..." : "Kirim ulang OTP"),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
