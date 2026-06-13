import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../services/api_service.dart';
import '../services/theme_service.dart';
import 'report_detail_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => HomeScreenState();
}

class HomeScreenState extends State<HomeScreen> {
  static const Color primaryBlue = Color(0xFF31479B);

  final searchController = TextEditingController();
  final imagePicker = ImagePicker();
  Map<String, dynamic>? currentUser;
  List reports = [];
  bool isLoading = true;
  String selectedType = 'all';

  @override
  void initState() {
    super.initState();
    fetchData();
  }

  @override
  void dispose() {
    searchController.dispose();
    super.dispose();
  }

  void fetchData() async {
    setState(() {
      isLoading = true;
    });

    try {
      final results = await Future.wait([
        ApiService.getCurrentUser(),
        ApiService.getReports(
          search: searchController.text,
          type: selectedType,
        ),
      ]);
      setState(() {
        currentUser = results[0] as Map<String, dynamic>;
        reports = results[1] as List;
        isLoading = false;
      });
    } catch (e) {
      print("ERROR: $e");
      setState(() {
        isLoading = false;
      });
    }
  }

  void searchReports() {
    fetchData();
  }

  void updateFilter(String type) {
    setState(() {
      selectedType = type;
    });
    fetchData();
  }

  void showFilterDialog() {
    showDialog(
      context: context,
      barrierColor: Colors.black26,
      builder: (_) => _FilterDialog(
        selectedType: selectedType,
        onSelected: (type) {
          Navigator.pop(context);
          updateFilter(type);
        },
      ),
    );
  }



  Future<void> openReportDetail(Map<String, dynamic> item) async {
    final id = (item['id'] ?? item['_id'] ?? '').toString();
    if (id.isEmpty) {
      return;
    }

    final shouldRefresh = await Navigator.push<bool>(
      context,
      MaterialPageRoute(
        builder: (_) => ReportDetailScreen(
          reportId: id,
          initialReport: item,
          isOwnerView: _isMyReport(item),
        ),
      ),
    );

    if (shouldRefresh == true && mounted) {
      fetchData();
    }
  }

  bool _isMyReport(Map<String, dynamic> item) {
    final userId = (currentUser?['id'] ?? currentUser?['_id'] ?? '').toString();
    final reportUserId = (item['user_id'] ?? '').toString();
    final nestedUser = item['user'];
    final nestedUserId = nestedUser is Map
        ? (nestedUser['id'] ?? nestedUser['_id'] ?? '').toString()
        : '';

    return userId.isNotEmpty &&
        (userId == reportUserId || userId == nestedUserId);
  }

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<bool>(
      valueListenable: ThemeService.isDarkMode,
      builder: (context, isDarkMode, _) {
        return Scaffold(
          backgroundColor: isDarkMode ? const Color(0xFF111827) : Colors.white,
          extendBody: true,
          appBar: AppBar(
            backgroundColor: primaryBlue,
            elevation: 0,
            title: const Text(
              "IPB Kehilangan",
              style: TextStyle(
                color: Colors.white,
                fontSize: 25,
                fontWeight: FontWeight.w800,
              ),
            ),
            automaticallyImplyLeading: false,
          ),
          body: SafeArea(
            bottom: false,
            child: isLoading
                ? const Center(child: CircularProgressIndicator())
                : Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _SearchHeader(
                        controller: searchController,
                        onSearch: searchReports,
                        onFilter: showFilterDialog,
                        isDarkMode: isDarkMode,
                      ),
                      Expanded(
                        child: reports.isEmpty
                            ? _EmptyState(isDarkMode: isDarkMode)
                            : GridView.builder(
                                padding:
                                    const EdgeInsets.fromLTRB(18, 0, 18, 104),
                                itemCount: reports.length,
                                gridDelegate:
                                    const SliverGridDelegateWithFixedCrossAxisCount(
                                  crossAxisCount: 2,
                                  childAspectRatio: 0.65,
                                  crossAxisSpacing: 18,
                                  mainAxisSpacing: 18,
                                ),
                                itemBuilder: (context, index) {
                                  final item = reports[index];
                                  return _HomeReportCard(
                                    item: item is Map
                                        ? Map<String, dynamic>.from(item)
                                        : const <String, dynamic>{},
                                    onTap: openReportDetail,
                                    isDarkMode: isDarkMode,
                                  );
                                },
                              ),
                      ),
                    ],
                  ),
          ),
        );
      },
    );
  }
}

class _SearchHeader extends StatelessWidget {
  const _SearchHeader({
    required this.controller,
    required this.onSearch,
    required this.onFilter,
    required this.isDarkMode,
  });

  final TextEditingController controller;
  final VoidCallback onSearch;
  final VoidCallback onFilter;
  final bool isDarkMode;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 26, 14, 28),
      child: Row(
        children: [
          Expanded(
            child: Container(
              height: 34,
              decoration: BoxDecoration(
                color: isDarkMode
                    ? const Color(0xFF1F2937)
                    : const Color(0xFFD8D8D8),
                borderRadius: BorderRadius.circular(24),
                boxShadow: const [
                  BoxShadow(
                    color: Color(0x33000000),
                    blurRadius: 14,
                    offset: Offset(0, 7),
                  ),
                ],
              ),
              child: TextField(
                controller: controller,
                textInputAction: TextInputAction.search,
                onSubmitted: (_) => onSearch(),
                onChanged: (value) {
                  if (value.trim().isEmpty) {
                    onSearch();
                  }
                },
                decoration: InputDecoration(
                  border: InputBorder.none,
                  prefixIcon: IconButton(
                    onPressed: onSearch,
                    icon: const Icon(
                      Icons.search,
                      color: Color(0xFF9CA0C3),
                      size: 28,
                    ),
                    tooltip: "Cari",
                  ),
                  contentPadding: const EdgeInsets.only(bottom: 12),
                ),
              ),
            ),
          ),
          const SizedBox(width: 18),
          IconButton(
            onPressed: onFilter,
            icon: const Icon(Icons.tune, color: Colors.black, size: 33),
            tooltip: "Filter",
          ),
        ],
      ),
    );
  }
}

class _DonationBanner extends StatelessWidget {
  const _DonationBanner();

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      color: HomeScreenState.primaryBlue,
      padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 31),
      child: const Text(
        "barang ga diambil ambil? kirim ke admin biar admin\n"
        "donasikan ke orang yang membutuhkan",
        textAlign: TextAlign.center,
        style: TextStyle(
          color: Colors.white,
          fontSize: 13,
          height: 1.35,
          fontWeight: FontWeight.w500,
        ),
      ),
    );
  }
}

class _FilterDialog extends StatelessWidget {
  const _FilterDialog({
    required this.selectedType,
    required this.onSelected,
  });

  final String selectedType;
  final ValueChanged<String> onSelected;

  @override
  Widget build(BuildContext context) {
    return Dialog(
      insetPadding: const EdgeInsets.symmetric(horizontal: 62),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
      child: Padding(
        padding: const EdgeInsets.fromLTRB(10, 20, 10, 16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              "Kategori",
              style: TextStyle(
                color: HomeScreenState.primaryBlue,
                fontSize: 17,
                fontWeight: FontWeight.w800,
              ),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: _FilterOptionButton(
                    label: "Dicari",
                    color: const Color(0xFF16C91F),
                    isSelected: selectedType == "Dicari",
                    onTap: () => onSelected("Dicari"),
                  ),
                ),
                const SizedBox(width: 22),
                Expanded(
                  child: _FilterOptionButton(
                    label: "Ditemukan",
                    color: const Color(0xFFE11D24),
                    isSelected: selectedType == "Ditemukan",
                    onTap: () => onSelected("Ditemukan"),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            TextButton(
              onPressed: () => onSelected("all"),
              child: Text(
                "Lihat Semua",
                style: TextStyle(
                  color: selectedType == "all"
                      ? HomeScreenState.primaryBlue
                      : const Color(0xFF8A8A8A),
                  fontSize: 14,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _FilterOptionButton extends StatelessWidget {
  const _FilterOptionButton({
    required this.label,
    required this.color,
    required this.isSelected,
    required this.onTap,
  });

  final String label;
  final Color color;
  final bool isSelected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: color,
      borderRadius: BorderRadius.circular(24),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(24),
        child: Container(
          height: 40,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(24),
            border: isSelected
                ? Border.all(color: HomeScreenState.primaryBlue, width: 3)
                : null,
          ),
          child: Text(
            label,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 14,
              fontWeight: FontWeight.w800,
            ),
          ),
        ),
      ),
    );
  }
}

class _HomeReportCard extends StatelessWidget {
  const _HomeReportCard({
    required this.item,
    required this.onTap,
    required this.isDarkMode,
  });

  final Map<String, dynamic> item;
  final ValueChanged<Map<String, dynamic>> onTap;
  final bool isDarkMode;

  @override
  Widget build(BuildContext context) {
    final title = (item['title'] ?? '-').toString();
    final location = (item['location'] ?? '-').toString();
    final description = (item['description'] ?? '-').toString();
    final type = (item['type'] ?? 'Dicari').toString();
    
    final uploaderName = _uploaderName(item['user']);
    final profilePhotoUrl = _profilePhotoUrl(item['user']);

    final images = item['images'];
    final imageUrl =
        images is List && images.isNotEmpty ? images.first?.toString() : null;

    return Material(
      color: isDarkMode ? const Color(0xFF1F2937) : Colors.white,
      borderRadius: BorderRadius.circular(18),
      child: InkWell(
        onTap: () => onTap(item),
        borderRadius: BorderRadius.circular(18),
        child: Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            border: Border.all(
              color: isDarkMode
                  ? const Color(0xFF374151)
                  : const Color(0xFFD8D8D8),
              width: 3,
            ),
            borderRadius: BorderRadius.circular(18),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  _StatusPill(label: type),
                  const Spacer(),
                  _SmallUploaderInfo(
                    name: uploaderName,
                    profilePhotoUrl: profilePhotoUrl,
                    isDarkMode: isDarkMode,
                  ),
                ],
              ),
              const SizedBox(height: 10),
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: SizedBox(
                  height: 118,
                  width: double.infinity,
                  child: imageUrl != null && imageUrl.isNotEmpty
                      ? Image.network(
                          imageUrl,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) =>
                              const _CardImageFallback(),
                        )
                      : const _CardImageFallback(),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                title,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: isDarkMode ? Colors.white : Colors.black,
                  fontSize: 14,
                  fontWeight: FontWeight.w800,
                ),
              ),
              const SizedBox(height: 2),
              Row(
                children: [
                  Icon(
                    Icons.location_pin,
                    color: isDarkMode ? Colors.white : Colors.black,
                    size: 14,
                  ),
                  Expanded(
                    child: Text(
                      location,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        color: isDarkMode ? Colors.white : Colors.black,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Expanded(
                child: Text(
                  description,
                  maxLines: 4, // More lines for description since no button
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    color: isDarkMode ? Colors.white : Colors.black,
                    fontSize: 12,
                    height: 1.25,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _uploaderName(dynamic user) {
    if (user is Map && user['name'] != null) {
      final name = user['name'].toString().trim();
      if (name.isNotEmpty) {
        return name;
      }
    }
    return 'User';
  }

  String? _profilePhotoUrl(dynamic user) {
    if (user is Map && user['profile_photo'] != null) {
      final profilePhoto = user['profile_photo'].toString().trim();
      if (profilePhoto.isNotEmpty) {
        return profilePhoto;
      }
    }
    return null;
  }
}

class _SmallUploaderInfo extends StatelessWidget {
  const _SmallUploaderInfo({
    required this.name,
    required this.profilePhotoUrl,
    required this.isDarkMode,
  });

  final String name;
  final String? profilePhotoUrl;
  final bool isDarkMode;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Text(
          name.length > 10 ? '${name.substring(0, 10)}...' : name,
          style: TextStyle(
            fontSize: 10,
            fontWeight: FontWeight.w600,
            color: isDarkMode ? Colors.white70 : Colors.black87,
          ),
        ),
        const SizedBox(width: 4),
        if (profilePhotoUrl != null && profilePhotoUrl!.isNotEmpty)
          ClipOval(
            child: Image.network(
              profilePhotoUrl!,
              width: 18,
              height: 18,
              fit: BoxFit.cover,
              errorBuilder: (_, __, ___) => _fallbackAvatar(),
            ),
          )
        else
          _fallbackAvatar(),
      ],
    );
  }

  Widget _fallbackAvatar() {
    return const CircleAvatar(
      radius: 9,
      backgroundColor: Color(0xFFE5E5E5),
      child: Icon(Icons.person, color: Color(0xFF9B9B9B), size: 12),
    );
  }
}

class _StatusPill extends StatelessWidget {
  const _StatusPill({required this.label});

  final String label;

  @override
  Widget build(BuildContext context) {
    final normalized = label.toLowerCase();
    final color = normalized == "selesai"
        ? const Color(0xFF31CF2D)
        : normalized == "ditemukan"
            ? const Color(0xFFE6B31B)
            : const Color(0xFFD24A4A);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        label,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 10,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }
}

class _CardImageFallback extends StatelessWidget {
  const _CardImageFallback();

  @override
  Widget build(BuildContext context) {
    return Container(
      color: const Color(0xFFF2F2F2),
      child: const Center(
        child: Icon(Icons.image_outlined, color: Color(0xFFBDBDBD), size: 44),
      ),
    );
  }
}

class _EmptyState extends StatelessWidget {
  const _EmptyState({required this.isDarkMode});

  final bool isDarkMode;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Text(
        "Belum ada upload terbaru",
        style: TextStyle(
          color: isDarkMode ? const Color(0xFFB8B8B8) : const Color(0xFF777777),
        ),
      ),
    );
  }
}



